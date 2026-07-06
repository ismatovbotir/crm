<?php

namespace App\Livewire\Portal\Tickets;

use App\Models\Support\Ticket;
use App\Models\Support\TicketCategory;
use App\Models\User;
use App\Notifications\NewTicketNotification;
use App\Services\TelegramService;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateForm extends Component
{
    public ?int $category_id = null;
    public string $priority = 'medium';
    public string $subject = '';
    public string $description = '';
    public string $serial_number = '';

    // Serial lookup state
    public string $ext_brand        = '';
    public string $ext_model        = '';
    public bool   $showExternalForm  = false;
    public ?array $foundSerial       = null;

    protected function rules(): array
    {
        return [
            'subject'       => 'required|string|max:255',
            'description'   => 'nullable|string|max:5000',
            'category_id'   => 'nullable|exists:ticket_categories,id',
            'priority'      => 'required|in:low,medium,high,critical',
            'serial_number' => 'nullable|string|max:100',
        ];
    }

    protected function messages(): array
    {
        return [
            'subject.required' => 'Укажите тему обращения.',
        ];
    }

    public function lookupSerial(): void
    {
        $this->showExternalForm = false;
        $this->foundSerial      = null;

        if (!trim($this->serial_number)) return;

        $customer = auth()->user()->customers()->first();

        $serial = \App\Models\Catalog\ProductSerial::with('currentOwner')
            ->where('serial_number', trim($this->serial_number))
            ->first();

        if ($serial) {
            $ownedByCustomer = $customer && $serial->current_owner_id === $customer->id;
            $this->foundSerial = [
                'id'             => $serial->id,
                'display_name'   => $serial->display_name,
                'current_status' => $serial->current_status,
                'is_external'    => $serial->is_external,
                'owned_by_me'    => $ownedByCustomer || $serial->is_external,
            ];
            if (!$this->foundSerial['owned_by_me']) {
                $this->addError('serial_number', 'Этот серийный номер не принадлежит вашей компании.');
                $this->foundSerial = null;
            }
        } else {
            $this->showExternalForm = true;
        }
    }

    public function save(): void
    {
        $data = $this->validate();
        $customer = auth()->user()->customers()->first();

        $serialId = null;
        if (trim($this->serial_number)) {
            if ($this->foundSerial) {
                $serialId = $this->foundSerial['id'];
                $serial = \App\Models\Catalog\ProductSerial::find($serialId);
                if ($serial && $serial->current_status !== 'in_repair') {
                    app(\App\Services\SerialService::class)->markInRepair($serial, 'Принято на сервис (тикет)');
                }
            } elseif ($this->showExternalForm) {
                $serial = app(\App\Services\SerialService::class)->registerExternal(
                    trim($this->serial_number),
                    $this->ext_brand ?: null,
                    $this->ext_model ?: null,
                    $customer?->id,
                );
                $serialId = $serial->id;
            }
        }

        $number = 'T-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $ticket = Ticket::create([
            'number'      => $number,
            'customer_id' => $customer?->id,
            'category_id' => $data['category_id'],
            'priority'    => $data['priority'],
            'subject'     => $data['subject'],
            'description' => $data['description'] ?: null,
            'status'      => 'open',
            'created_by'  => auth()->id(),
            'serial_id'   => $serialId,
        ]);

        // Notify tech support team
        User::role('tech-support')->get()->each(function ($user) use ($ticket) {
            $user->notify(new NewTicketNotification($ticket));
        });

        TelegramService::send(
            "🎫 <b>Новый тикет</b>\n" .
            "#{$ticket->id}: {$ticket->subject}\n" .
            "Приоритет: {$ticket->priority}\n" .
            "Клиент: " . (auth()->user()->customers()->first()?->name ?? auth()->user()->name)
        );

        session()->flash('success', 'Тикет создан. Мы ответим в ближайшее время.');
        $this->dispatch('ticket-saved');
        $this->reset(['subject', 'description', 'category_id', 'priority', 'serial_number', 'ext_brand', 'ext_model']);
        $this->priority         = 'medium';
        $this->showExternalForm = false;
        $this->foundSerial      = null;
    }

    public function render()
    {
        return view('livewire.portal.tickets.create-form', [
            'categories' => TicketCategory::orderBy('name')->get(),
        ])->layout('layouts.portal')->section('content');
    }
}
