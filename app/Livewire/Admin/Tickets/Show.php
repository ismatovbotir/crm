<?php

namespace App\Livewire\Admin\Tickets;

use App\Models\Support\Ticket;
use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public Ticket $ticket;
    public string $commentBody = '';
    public bool $isInternal = false;
    public ?int $assigneeId = null;

    public function mount(Ticket $ticket): void
    {
        $this->authorize('view', $ticket);
        $this->ticket = $ticket->load([
            'customer',
            'contact',
            'category',
            'assignee',
            'creator',
            'comments.user',
            'attachments',
            'serial.statusHistory',
            'serial.ownerHistory.customer',
            'serial.tickets' => fn ($q) => $q->latest()->limit(5),
        ]);
        $this->assigneeId = $ticket->assignee_id;
    }

    public function addComment(): void
    {
        $this->validate(['commentBody' => 'required|string|max:10000']);

        $this->ticket->comments()->create([
            'user_id'     => auth()->id(),
            'body'        => $this->commentBody,
            'is_internal' => $this->isInternal,
        ]);

        $this->commentBody = '';
        $this->isInternal = false;
        $this->ticket->load('comments.user');
    }

    public function changeStatus(string $status): void
    {
        $this->authorize('update', $this->ticket);
        $allowed = ['open', 'in_progress', 'pending_customer', 'resolved', 'closed'];
        if (! in_array($status, $allowed)) return;

        $updates = ['status' => $status];
        if ($status === 'resolved') $updates['resolved_at'] = now();
        if ($status === 'closed') $updates['closed_at'] = now();

        $this->ticket->update($updates);
        $this->ticket->refresh()->load([
            'customer',
            'contact',
            'category',
            'assignee',
            'creator',
            'comments.user',
            'serial.statusHistory',
            'serial.ownerHistory.customer',
            'serial.tickets' => fn ($q) => $q->latest()->limit(5),
        ]);
    }

    public function updatedAssigneeId(?int $value): void
    {
        $this->authorize('update', $this->ticket);
        $this->ticket->update(['assignee_id' => $value]);
        $this->ticket->refresh()->load('assignee');
    }

    public function render()
    {
        return view('livewire.admin.tickets.show', [
            'supportStaff' => User::role(['tech-support', 'super-admin'])->active()->get(),
        ]);
    }
}
