<?php

namespace App\Policies;

use App\Models\Customer\Customer;
use App\Models\User;

/**
 * RSG-CRM — Customer Policy
 *
 * 3-й уровень защиты (Routes → UI → Policies).
 * Контролирует доступ к КОНКРЕТНОМУ клиенту.
 *
 * - super-admin / sales-director: видят всех клиентов
 * - sales-manager: только своих (через customer_users)
 */
class CustomerPolicy
{
    /**
     * Может ли видеть список клиентов вообще.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('customers.view');
    }

    /**
     * Может ли видеть конкретного клиента.
     */
    public function view(User $user, Customer $customer): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) {
            return true;
        }

        return $user->can('customers.view')
            && $customer->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Может ли создавать клиентов.
     */
    public function create(User $user): bool
    {
        return $user->can('customers.create');
    }

    /**
     * Может ли изменять клиента.
     */
    public function update(User $user, Customer $customer): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) {
            return $user->can('customers.update');
        }

        return $user->can('customers.update')
            && $customer->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Может ли удалять клиента.
     */
    public function delete(User $user, Customer $customer): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director'])) {
            return $user->can('customers.delete');
        }

        return $user->can('customers.delete')
            && $customer->users()->where('users.id', $user->id)->exists();
    }
}
