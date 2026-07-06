<?php

namespace App\Livewire\Portal\Tickets;

use App\Models\Support\Ticket;
use App\Models\Support\TicketCategory;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public bool $showCreateForm = false;

    public function openCreate(): void
    {
        $this->showCreateForm = true;
    }

    public function closeForm(): void
    {
        $this->showCreateForm = false;
    }

    #[On('ticket-saved')]
    public function onTicketSaved(): void
    {
        $this->showCreateForm = false;
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $customer = auth()->user()->customers()->first();

        $query = Ticket::where('customer_id', $customer?->id)
            ->with(['category', 'assignee'])
            ->latest();

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.portal.tickets.index', [
            'tickets'    => $query->paginate(15),
            'categories' => TicketCategory::orderBy('name')->get(),
        ]);
    }
}
