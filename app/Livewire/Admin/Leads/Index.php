<?php

namespace App\Livewire\Admin\Leads;

use App\Models\Lead\Lead;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 15;

    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Lead::class);
    }

    protected function baseQuery(): Builder
    {
        $search = $this->search;

        $query = Lead::query()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when(! $this->statusFilter, fn ($q) => $q->where('status', '!=', 'client'));

        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (! $user->hasAnyRole(['super-admin', 'sales-director'])) {
            $query->where('manager_id', auth()->id());
        }

        return $query;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $statuses = ['new', 'qualified', 'contacted', 'in_negotiation', 'won', 'lost'];

        $leads = $this->baseQuery()
            ->with(['manager', 'creator', 'source', 'businessType'])
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.leads.index', [
            'leads'    => $leads,
            'statuses' => $statuses,
        ]);
    }
}
