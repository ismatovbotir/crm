<?php

namespace App\Policies;

use App\Models\Lead\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) return true;
        return $user->can('leads.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) return true;
        return $user->can('leads.view') && $lead->manager_id === $user->id;
    }

    public function create(User $user): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) return true;
        return $user->can('leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) return true;
        return $user->can('leads.update') && $lead->manager_id === $user->id;
    }

    public function delete(User $user, Lead $lead): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) return true;
        return $user->can('leads.delete') && $lead->manager_id === $user->id;
    }

    public function assign(User $user, Lead $lead): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) return true;
        return $user->can('leads.assign');
    }
}
