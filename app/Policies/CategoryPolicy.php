<?php

namespace App\Policies;

use App\Models\Catalog\Category;
use App\Models\User;

class CategoryPolicy
{
    private function canView(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'sales-director', 'sales-manager', 'catalog-manager', 'accountant', 'tech-support'])
            || $user->can('catalog.products.view');
    }

    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, Category $_category): bool
    {
        return $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'catalog-manager'])
            || $user->can('catalog.products.create');
    }

    public function update(User $user, ?Category $_category = null): bool
    {
        return $user->hasAnyRole(['super-admin', 'catalog-manager'])
            || $user->can('catalog.products.update');
    }

    public function delete(User $user, Category $_category): bool
    {
        return $user->hasAnyRole(['super-admin', 'catalog-manager'])
            || $user->can('catalog.products.delete');
    }
}
