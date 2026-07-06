<?php

namespace App\Livewire\Admin\Tickets;

use App\Models\Customer\Customer;
use App\Models\Support\Ticket;
use App\Models\Support\TicketCategory;
use Livewire\Component;

class CreateForm extends Component
{
    public ?int $customer_id = null;
    public ?int $category_id = null;
    public string $priority = 'medium';
    public string $subject = '';
    public string $description = '';
    public string $serial_number = '';

    // Serial lookup state
    public string  $ext_brand        = '';
    public string  $ext_model        = '';
    public bool    $showExternalForm  = false;
    public ?array  $foundSerial       = null; // ['id', 'display_name', 'current_status', 'owner_name']

    protected function rules(): array
    {
        return [
            'customer_id'   => 'nullable|exists:customers,id',
            'category_id'   => 'nullable|exists:ticket_categories,id',
            'priority'      => 'required|in:low,medium,high,critical',
            'subject'       => 'required|string|max:500',
            'description'   => 'nullable|string|max:10000',
            'serial_number' => 'nullable|string|max:100',
        ];
    }

    public function lookupSerial(): void
    {
        $this->showExternalForm = false;
        $this->foundSerial      = null;

        if (!trim($this->serial_number)) return;

        $serial = \App\Models\Catalog\ProductSerial::with('currentOwner')
            ->where('serial_number', trim($this->serial_number))
            ->first();

        if ($serial) {
            $this->foundSerial = [
                'id'             => $serial->id,
                'display_name'   => $serial->display_name,
                'current_status' => $serial->current_status,
                'owner_name'     => $serial->currentOwner?->name,
            ];
        } else {
            $this->showExternalForm = true;
        }
    }

    public function save(): void
    {
        $this->authorize('create', Ticket::class);

        $data = $this->validate();

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
                    $this->customer_id ?: null,
                );
                $serialId = $serial->id;
            }
        }

        Ticket::create(array_merge($data, [
            'number'     => 'TKT-' . str_pad(Ticket::withTrashed()->count() + 1, 5, '0', STR_PAD_LEFT),
            'created_by' => auth()->id(),
            'status'     => 'open',
            'serial_id'  => $serialId,
        ]));

        session()->flash('success', 'Тикет создан.');
        $this->dispatch('ticket-saved');
        $this->reset(['subject', 'description', 'customer_id', 'category_id', 'serial_number', 'ext_brand', 'ext_model']);
        $this->showExternalForm = false;
        $this->foundSerial      = null;
    }

    public function render()
    {
        return view('livewire.admin.tickets.create-form', [
            'customers'  => Customer::active()->orderBy('name')->limit(200)->get(),
            'categories' => TicketCategory::active()->get(),
        ]);
    }
}
