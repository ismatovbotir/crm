<?php

namespace App\Policies;

use App\Models\Support\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool { return $user->can('tickets.view'); }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) return true;
        return $user->can('tickets.view') && ($ticket->assignee_id === $user->id || $ticket->created_by === $user->id);
    }

    public function create(User $user): bool { return $user->can('tickets.view'); }
    public function update(User $user, Ticket $ticket): bool { return $user->can('tickets.update'); }
    public function assign(User $user): bool { return $user->can('tickets.assign'); }
    public function close(User $user): bool { return $user->can('tickets.close'); }
}
