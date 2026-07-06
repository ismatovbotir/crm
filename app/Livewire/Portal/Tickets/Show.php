<?php

namespace App\Livewire\Portal\Tickets;

use App\Models\Support\Ticket;
use App\Models\Support\TicketComment;
use Livewire\Component;

class Show extends Component
{
    public Ticket $ticket;
    public string $commentBody = '';

    public function mount(Ticket $ticket): void
    {
        // Check that this ticket belongs to ANY of the user's companies (not just the first one).
        abort_unless(
            auth()->user()->customers()->where('customers.id', $ticket->customer_id)->exists(),
            403
        );

        $this->ticket = $ticket;
    }

    protected function rules(): array
    {
        return ['commentBody' => 'required|string|max:5000'];
    }

    public function addComment(): void
    {
        $this->validate();

        TicketComment::create([
            'ticket_id'   => $this->ticket->id,
            'user_id'     => auth()->id(),
            'body'        => $this->commentBody,
            'is_internal' => false,
        ]);

        $this->commentBody = '';
        $this->ticket->refresh();
    }

    public function render()
    {
        return view('livewire.portal.tickets.show', [
            'comments' => $this->ticket->publicComments()->with('user')->get(),
        ]);
    }
}
