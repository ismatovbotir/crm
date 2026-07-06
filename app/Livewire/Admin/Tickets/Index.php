<?php

namespace App\Livewire\Admin\Tickets;

use App\Models\Support\Ticket;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $priorityFilter = '';
    public int $perPage = 20;
    public bool $showCreateForm = false;

    protected $queryString = ['search' => ['except' => ''], 'statusFilter' => ['except' => ''], 'priorityFilter' => ['except' => '']];

    public function mount(): void
    {
        $this->authorize('viewAny', Ticket::class);
    }

    public function getTicketsProperty()
    {
        $q = Ticket::with(['customer', 'category', 'assignee'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('number', 'like', "%{$this->search}%")
                  ->orWhere('subject', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->priorityFilter, fn ($q) => $q->where('priority', $this->priorityFilter));

        if (auth()->user()->hasRole('tech-support') && ! auth()->user()->hasAnyRole(['super-admin', 'sales-director'])) {
            $q->where('assignee_id', auth()->id());
        }

        return $q->latest()->paginate($this->perPage);
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingPriorityFilter(): void { $this->resetPage(); }

    public function closeForm(): void { $this->showCreateForm = false; }

    #[\Livewire\Attributes\On('ticket-saved')]
    public function onTicketSaved(): void { $this->showCreateForm = false; }

    public function render()
    {
        return view('livewire.admin.tickets.index', [
            'tickets'    => $this->tickets,
            'statuses'   => ['open', 'in_progress', 'pending_customer', 'resolved', 'closed'],
            'priorities' => ['low', 'medium', 'high', 'critical'],
        ]);
    }
}
