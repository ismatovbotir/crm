<?php

namespace App\Policies;

use App\Models\Sell\Sell;
use App\Models\User;

class SellPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super-admin', 'sales-director', 'sales-manager', 'accountant']);
    }

    public function view(User $user, Sell $sell): bool
    {
        if ($user->hasRole(['super-admin', 'sales-director', 'accountant'])) {
            return true;
        }

        return $sell->manager_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super-admin', 'sales-director', 'sales-manager']);
    }

    public function update(User $user, Sell $sell): bool
    {
        if ($user->hasRole(['super-admin', 'sales-director'])) {
            return true;
        }

        return $sell->manager_id === $user->id && $sell->status === 'draft';
    }

    public function delete(User $user, Sell $sell): bool
    {
        return $user->hasRole(['super-admin', 'sales-director']);
    }
}
