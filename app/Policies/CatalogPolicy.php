<?php

namespace App\Policies;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasAnyRole(['super-admin', 'sales-director', 'sales-manager', 'catalog-manager', 'accountant', 'tech-support'])) return true;
        return $user->can('catalog.products.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $this->viewAny($user);
    }
}
