<?php

namespace App\Livewire\Admin\Catalog\Categories;

use App\Models\Catalog\Category;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Category::class);
    }

    public function render()
    {
        $categories = Category::withCount(['products', 'children'])
            ->when($this->search, fn ($q) => $q->where('name_ru', 'like', "%{$this->search}%"))
            ->orderBy('sort_order')
            ->get();

        return view('livewire.admin.catalog.categories.index', [
            'categories' => $categories,
        ]);
    }
}
