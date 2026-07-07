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

    /**
     * Table vs Kanban view toggle. Persisted client-side via localStorage
     * (key `rsg-admin-leads-view-mode`, mirroring the `rsg-admin-sidebar-collapsed`
     * pattern in layouts/admin.blade.php) — the Alpine/Blade side is owned by
     * admin-bi-developer; this property is just the server-side switch.
     */
    public string $viewMode = 'table';

    /**
     * Statuses that get a Kanban column. `client` is intentionally excluded:
     * it's a system/terminal status only ever set by Show::convertToCustomer(),
     * never a manually selectable value (see EditForm::rules() and
     * Show::changeStatus()'s whitelist) — consistent with that decision here.
     */
    protected const KANBAN_STATUSES = ['new', 'qualified', 'contacted', 'in_negotiation', 'won', 'lost'];

    /** Cap per Kanban column to avoid loading unbounded rows in the first iteration. */
    protected const KANBAN_COLUMN_LIMIT = 50;

    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Lead::class);
    }

    /**
     * Shared scope (search + manager ownership) used by both the table query
     * and the Kanban columns query, so both views are guarded identically.
     */
    protected function scopedQuery(): Builder
    {
        $search = $this->search;

        $query = Lead::query()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            }));

        /** @var \App\Models\User $user */
        $user = auth()->user();
        if (! $user->hasAnyRole(['super-admin', 'sales-director'])) {
            $query->where('manager_id', auth()->id());
        }

        return $query;
    }

    protected function baseQuery(): Builder
    {
        $search = $this->search;

        return $this->scopedQuery()
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when(! $this->statusFilter && ! $search, fn ($q) => $q->where('status', '!=', 'client'));
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

    public function setViewMode(string $mode): void
    {
        if (! in_array($mode, ['table', 'kanban'], true)) {
            return;
        }

        $this->viewMode = $mode;
    }

    /**
     * Builds the 6 Kanban columns (new/qualified/contacted/in_negotiation/won/lost).
     *
     * Decision: `statusFilter` is intentionally ignored here — in Kanban mode the
     * status is already conveyed by the column itself, so filtering by a single
     * status would just empty out the other 5 columns for no benefit. `search`
     * IS respected (via scopedQuery()), consistently with the table view.
     */
    protected function kanbanColumns(): array
    {
        $columns = [];

        foreach (self::KANBAN_STATUSES as $status) {
            $query = $this->scopedQuery()->where('status', $status);

            $total = (clone $query)->count();

            $leads = $query
                ->with(['manager', 'creator', 'source', 'businessType'])
                ->latest()
                ->limit(self::KANBAN_COLUMN_LIMIT)
                ->get();

            $columns[$status] = [
                'leads'     => $leads,
                'total'     => $total,
                'remaining' => max(0, $total - $leads->count()),
            ];
        }

        return $columns;
    }

    public function moveLeadStatus(int $leadId, string $status): void
    {
        $lead = Lead::findOrFail($leadId);

        $this->authorize('update', $lead);

        if ($lead->status === 'client') {
            return;
        }

        if (! in_array($status, self::KANBAN_STATUSES, true)) {
            return;
        }

        if ($status === $lead->status) {
            return;
        }

        $oldStatus = $lead->status;
        $lead->update(['status' => $status]);

        $lead->activities()->create([
            'user_id'     => auth()->id(),
            'type'        => 'status_change',
            'title'       => 'Изменён статус',
            'description' => "Статус изменён на: {$status}",
            'meta'        => ['from' => $oldStatus, 'to' => $status],
        ]);
    }

    public function render()
    {
        $statuses = ['new', 'qualified', 'contacted', 'in_negotiation', 'won', 'lost', 'client'];

        $leads = $this->baseQuery()
            ->with(['manager', 'creator', 'source', 'businessType'])
            ->latest()
            ->paginate($this->perPage);

        $kanbanColumns = $this->viewMode === 'kanban' ? $this->kanbanColumns() : [];

        return view('livewire.admin.leads.index', [
            'leads'          => $leads,
            'statuses'       => $statuses,
            'kanbanColumns'  => $kanbanColumns,
            'kanbanStatuses' => self::KANBAN_STATUSES,
        ]);
    }
}
