<?php

namespace App\Policies;

use App\Models\Invoice\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool { return $user->can('invoices.view'); }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director', 'accountant'])) return true;
        return $user->can('invoices.view') && $invoice->manager_id === $user->id;
    }

    public function create(User $user): bool { return $user->can('invoices.create'); }

    public function update(User $user, Invoice $invoice): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director', 'accountant'])) return $user->can('invoices.update');
        return $user->can('invoices.update') && $invoice->manager_id === $user->id;
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyRole(['super-admin', 'accountant']) && $user->can('invoices.cancel');
    }
}
