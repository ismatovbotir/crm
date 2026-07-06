<?php

namespace App\Livewire\Portal\EquipmentRequests;

use App\Models\Support\EquipmentRequest;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $customer = auth()->user()->customers()->first();

        $query = EquipmentRequest::where('customer_id', $customer?->id)
            ->with('manager')
            ->latest();

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.portal.equipment-requests.index', [
            'requests' => $query->paginate(15),
        ])->layout('layouts.portal')->section('content');
    }
}
