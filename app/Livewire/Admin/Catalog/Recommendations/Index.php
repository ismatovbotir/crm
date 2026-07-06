<?php

namespace App\Livewire\Admin\Catalog\Recommendations;

use App\Models\BusinessType;
use App\Models\Catalog\BusinessTypeRecommendation;
use App\Models\Catalog\Product;
use Livewire\Component;

class Index extends Component
{
    public ?int $selectedTypeId = null;
    public string $productSearch = '';
    public string $newPriority = 'recommended';
    public ?int $newProductId = null;

    // ── Validation ────────────────────────────────────────────────────────

    protected function addRules(): array
    {
        return [
            'selectedTypeId' => 'required|exists:business_types,id',
            'newProductId'   => 'required|exists:products,id',
            'newPriority'    => 'required|in:required,recommended,optional',
        ];
    }

    protected function messages(): array
    {
        return [
            'selectedTypeId.required' => 'Выберите тип бизнеса.',
            'newProductId.required'   => 'Выберите товар для добавления.',
            'newProductId.exists'     => 'Выбранный товар не найден.',
            'newPriority.in'          => 'Допустимые значения приоритета: required, recommended, optional.',
        ];
    }

    // ── Lifecycle ─────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);
    }

    // ── Type selection ────────────────────────────────────────────────────

    public function selectType(int $id): void
    {
        $this->selectedTypeId = $id;
        $this->reset('productSearch', 'newProductId');
        $this->resetValidation();
    }

    // ── Recommendation actions ────────────────────────────────────────────

    public function addRecommendation(): void
    {
        $this->authorize('update', Product::class);

        $this->validate($this->addRules(), $this->messages());

        $maxOrder = BusinessTypeRecommendation::where('business_type_id', $this->selectedTypeId)
            ->max('sort_order') ?? 0;

        BusinessTypeRecommendation::firstOrCreate(
            [
                'business_type_id' => $this->selectedTypeId,
                'product_id'       => $this->newProductId,
            ],
            [
                'priority'   => $this->newPriority,
                'sort_order' => $maxOrder + 1,
            ]
        );

        $this->reset('newProductId', 'productSearch');

        session()->flash('success', 'Товар добавлен в рекомендации.');
    }

    public function updatePriority(int $id, string $priority): void
    {
        $this->authorize('update', Product::class);

        if (! in_array($priority, ['required', 'recommended', 'optional'], true)) {
            return;
        }

        BusinessTypeRecommendation::findOrFail($id)->update(['priority' => $priority]);
    }

    public function removeRecommendation(int $id): void
    {
        $this->authorize('update', Product::class);

        BusinessTypeRecommendation::findOrFail($id)->delete();

        session()->flash('success', 'Рекомендация удалена.');
    }

    // ── Product search helper ─────────────────────────────────────────────

    /**
     * Select a product from search results and clear the search input.
     */
    public function selectProduct(int $productId): void
    {
        $this->newProductId   = $productId;
        $this->productSearch  = '';
    }

    // ── Render ────────────────────────────────────────────────────────────

    public function render()
    {
        $recommendations = collect();

        if ($this->selectedTypeId) {
            $recommendations = BusinessTypeRecommendation::where('business_type_id', $this->selectedTypeId)
                ->with(['product.category.group'])
                ->orderByRaw("CASE priority WHEN 'required' THEN 1 WHEN 'recommended' THEN 2 ELSE 3 END")
                ->orderBy('sort_order')
                ->get();
        }

        // Products for the dropdown/autocomplete: only active, not yet recommended for this type
        $existingProductIds = $recommendations->pluck('product_id')->toArray();

        $productSearchResults = [];

        if (strlen($this->productSearch) >= 2) {
            $productSearchResults = Product::where('is_active', true)
                ->whereNotIn('id', $existingProductIds)
                ->where(fn ($q) => $q
                    ->where('name_ru', 'like', "%{$this->productSearch}%")
                    ->orWhere('sku', 'like', "%{$this->productSearch}%")
                )
                ->limit(10)
                ->get(['id', 'name_ru', 'sku'])
                ->toArray();
        }

        // Resolve selected product name for display in the form
        $selectedProduct = $this->newProductId
            ? Product::find($this->newProductId, ['id', 'name_ru', 'sku'])
            : null;

        return view('livewire.admin.catalog.recommendations.index', [
            'businessTypes'        => BusinessType::where('is_active', true)->withCount('recommendations')->orderBy('sort_order')->get(),
            'recommendations'      => $recommendations,
            'productSearchResults' => $productSearchResults,
            'selectedProduct'      => $selectedProduct,
        ])->layout('layouts.admin')->section('content');
    }
}
