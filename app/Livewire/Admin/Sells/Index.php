<?php

namespace App\Livewire\Admin\Sells;

use App\Models\Sell\Sell;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public bool $showCreateForm = false;

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function getSellsProperty()
    {
        $q = Sell::with(['customer', 'invoice', 'manager'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('number', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus));

        if (! auth()->user()->hasAnyRole(['super-admin', 'sales-director', 'accountant'])) {
            $q->where('manager_id', auth()->id());
        }

        return $q->latest()->paginate(15);
    }

    public function deleteSell(Sell $sell): void
    {
        $this->authorize('delete', $sell);
        $sell->delete();
        session()->flash('success', "Отгрузка «{$sell->number}» удалена.");
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    #[\Livewire\Attributes\On('sell-created')]
    public function onSellCreated(): void { $this->showCreateForm = false; }

    public function render()
    {
        return view('livewire.admin.sells.index', [
            'sells' => $this->sells,
        ]);
    }
}
