<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Models\Catalog\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public Product $product;
    public bool $showEdit = false;

    public function mount(Product $product): void
    {
        $this->authorize('view', $product);
        $this->product = $product->load([
            'category',
            'prices',
            'stock',
            'images',
            'attachments',
            'attributeValues.attribute',
        ]);
    }

    public function openEdit(): void
    {
        $this->showEdit = true;
    }

    public function closeForm(): void
    {
        $this->showEdit = false;
    }

    #[On('product-saved')]
    public function onProductSaved(): void
    {
        $this->showEdit = false;
        $this->product = $this->product->fresh([
            'category', 'prices', 'stock', 'images', 'attachments', 'attributeValues.attribute',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.catalog.products.show');
    }
}
