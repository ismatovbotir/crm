<?php

namespace App\Livewire\Admin\Leads;

use App\Models\Lead\Lead;
use Livewire\Component;

class Show extends Component
{
    public Lead $lead;
    public string $noteText = '';
    public bool $showEditForm = false;

    public string $newStatus = '';
    public bool $showConvertForm = false;
    public string $convertName = '';
    public string $convertPhone = '';
    public string $convertEmail = '';
    public string $convertRegion = '';

    public function mount(Lead $lead): void
    {
        $this->authorize('view', $lead);
        $this->lead = $lead->load(['manager', 'source', 'businessType', 'customer', 'activities.user']);
    }

    public function addNote(): void
    {
        $this->authorize('update', $this->lead);
        $this->validate(['noteText' => 'required|string|max:2000']);

        $this->lead->activities()->create([
            'user_id'     => auth()->id(),
            'type'        => 'note',
            'title'       => 'Заметка',
            'description' => $this->noteText,
        ]);

        $this->noteText = '';
        $this->lead->load('activities.user');
    }

    public function applyStatus(): void
    {
        if ($this->lead->status === 'client') return;
        if ($this->newStatus) {
            $this->changeStatus($this->newStatus);
            $this->newStatus = '';
        }
    }

    public function changeStatus(string $status): void
    {
        $this->authorize('update', $this->lead);

        if ($this->lead->status === 'client') return;

        $allowed = ['new', 'qualified', 'contacted', 'in_negotiation', 'won', 'lost'];
        if (! in_array($status, $allowed)) {
            return;
        }

        $oldStatus = $this->lead->status;
        $this->lead->update(['status' => $status]);

        $this->lead->activities()->create([
            'user_id'     => auth()->id(),
            'type'        => 'status_change',
            'title'       => 'Изменён статус',
            'description' => "Статус изменён на: {$status}",
            'meta'        => ['from' => $oldStatus, 'to' => $status],
        ]);

        $this->lead->refresh()->load('activities.user');
    }

    public function openConvertForm(): void
    {
        $this->authorize('update', $this->lead);
        $this->convertName   = $this->lead->company ?: $this->lead->name;
        $this->convertPhone  = $this->lead->phone ?? '';
        $this->convertEmail  = $this->lead->email ?? '';
        $this->convertRegion = $this->lead->region ?? '';
        $this->showConvertForm = true;
    }

    public function convertToCustomer(): void
    {
        $this->authorize('update', $this->lead);
        abort_unless(! $this->lead->customer_id, 422, 'Уже конвертирован');

        $this->validate([
            'convertName'   => 'required|string|max:255',
            'convertPhone'  => 'nullable|string|max:50',
            'convertEmail'  => 'nullable|email|max:255',
            'convertRegion' => 'nullable|string|max:100',
        ]);

        $customer = \App\Models\Customer\Customer::create([
            'name'             => $this->convertName,
            'phone'            => $this->convertPhone ?: null,
            'email'            => $this->convertEmail ?: null,
            'region'           => $this->convertRegion ?: null,
            'business_type_id' => $this->lead->business_type_id,
            'status'           => 'active',
            'customer_since'   => today(),
        ]);

        $customer->contacts()->create([
            'name'       => $this->lead->name,
            'phone'      => $this->lead->phone,
            'email'      => $this->lead->email,
            'is_primary' => true,
        ]);

        $this->lead->update([
            'customer_id'  => $customer->id,
            'status'       => 'client',
            'converted_at' => now(),
        ]);

        $this->lead->activities()->create([
            'user_id'     => auth()->id(),
            'type'        => 'conversion',
            'title'       => 'Лид конвертирован в клиента',
            'description' => "Создан клиент: {$customer->name}",
            'meta'        => ['customer_id' => $customer->id],
        ]);

        $this->showConvertForm = false;
        $this->lead->refresh()->load(['manager', 'source', 'businessType', 'customer', 'activities.user']);
        session()->flash('success', "Клиент «{$customer->name}» создан.");
    }

    public function closeForm(): void
    {
        $this->showEditForm = false;
        $this->showConvertForm = false;
    }

    #[\Livewire\Attributes\On('lead-saved')]
    public function onLeadSaved(): void
    {
        $this->showEditForm = false;
        $this->lead->refresh()->load(['manager', 'source', 'businessType', 'customer', 'activities.user']);
    }

    public function render()
    {
        return view('livewire.admin.leads.show')
            ->layout('layouts.admin', ['mainClass' => '']);
    }
}
