<?php

namespace App\Livewire\Portal\Catalog;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $categoryFilter = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::visiblePortal()->with(['category', 'prices', 'primaryImage']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name_ru', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('brand', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        return view('livewire.portal.catalog.index', [
            'products'   => $query->paginate(24),
            'categories' => Category::whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
