<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $activeFilter = '';
    public int $perPage = 20;
    public bool $showCreate = false;

    protected $queryString = [
        'search'         => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'activeFilter'   => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }
    public function updatingActiveFilter(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->showCreate = true;
    }

    public function closeForm(): void
    {
        $this->showCreate = false;
    }

    #[On('product-saved')]
    public function onProductSaved(): void
    {
        $this->showCreate = false;
    }

    public function render()
    {
        $products = Product::with(['category', 'stock', 'prices'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name_ru', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%")
                ->orWhere('brand', 'like', "%{$this->search}%")
            ))
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->activeFilter !== '', fn ($q) => $q->where('is_active', (bool) $this->activeFilter))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.catalog.products.index', [
            'products'   => $products,
            'categories' => Category::where('is_active', true)->orderBy('name_ru')->get(),
        ]);
    }
}
