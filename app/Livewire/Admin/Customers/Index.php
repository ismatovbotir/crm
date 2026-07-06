<?php

namespace App\Livewire\Admin\Customers;

use App\Models\Customer\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $segmentFilter = '';
    public int $perPage = 15;

    protected $queryString = [
        'search'        => ['except' => ''],
        'statusFilter'  => ['except' => ''],
        'segmentFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Customer::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSegmentFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Customer::with(['businessType', 'bank'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('inn', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->segmentFilter, fn ($q) => $q->where('segment', $this->segmentFilter));

        if (! auth()->user()->hasAnyRole(['super-admin', 'sales-director'])) {
            $query->forUser(auth()->id());
        }

        $customers = $query->latest()->paginate($this->perPage);

        return view('livewire.admin.customers.index', [
            'customers' => $customers,
            'statuses'  => ['active', 'vip', 'inactive', 'blocked'],
            'segments'  => ['A', 'B', 'C'],
        ]);
    }
}
