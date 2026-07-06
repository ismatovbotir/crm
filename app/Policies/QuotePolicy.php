<?php

namespace App\Policies;

use App\Models\Quote\Quote;
use App\Models\User;

class QuotePolicy
{
    public function viewAny(User $user): bool { return $user->can('quotes.view'); }

    public function view(User $user, Quote $quote): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) return true;
        return $user->can('quotes.view') && $quote->manager_id === $user->id;
    }

    public function create(User $user): bool { return $user->can('quotes.create'); }

    public function update(User $user, Quote $quote): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) return $user->can('quotes.update');
        return $user->can('quotes.update') && $quote->manager_id === $user->id;
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $user->hasAnyRole(['super-admin', 'sales-director']) && $user->can('quotes.delete');
    }

    public function send(User $user, Quote $quote): bool { return $user->can('quotes.send'); }
}
