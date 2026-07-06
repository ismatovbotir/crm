<?php

namespace App\Livewire\Admin\EquipmentRequests;

use App\Models\Support\EquipmentRequest;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $managerFilter = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingManagerFilter(): void { $this->resetPage(); }

    #[Computed]
    public function requests()
    {
        return EquipmentRequest::with(['customer', 'manager'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('subject', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->managerFilter, fn ($q) => $q->where('manager_id', $this->managerFilter))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function managers()
    {
        return User::managers()->orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.admin.equipment-requests.index');
    }
}
