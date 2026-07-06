<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EditForm extends Component
{
    public Product $product;
    public int $productId;

    public string $sku = '';
    public string $name_ru = '';
    public string $name_uz = '';
    public string $brand = '';
    public string $model_number = '';
    public string $description_ru = '';
    public ?int $category_id = null;
    public string $unit = 'шт';
    public bool $is_active = true;
    public bool $is_visible_portal = true;
    public bool $is_serial = false;

    // Pricing
    public string $retail_price = '';
    public string $wholesale_price = '';
    public string $cost_price = '';
    public string $currency = 'UZS';

    public function mount(int $productId): void
    {
        $this->productId = $productId;
        $this->product = Product::with('prices')->findOrFail($productId);
        $this->authorize('update', $this->product);

        $attributes = $this->product->only([
            'sku', 'name_ru', 'name_uz', 'brand', 'model_number',
            'description_ru', 'category_id', 'unit',
            'is_active', 'is_visible_portal', 'is_serial',
        ]);

        // Nullable DB columns (name_uz, brand, model_number, description_ru) may
        // legitimately be null; coalesce to '' to match the string-typed props,
        // consistent with CreateForm's string-based fields.
        foreach (['name_uz', 'brand', 'model_number', 'description_ru'] as $nullableField) {
            $attributes[$nullableField] = $attributes[$nullableField] ?? '';
        }

        $this->fill($attributes);

        // Load existing prices into form fields
        foreach ($this->product->prices as $price) {
            match ($price->type) {
                'retail'    => $this->retail_price = (string) $price->amount,
                'wholesale' => $this->wholesale_price = (string) $price->amount,
                'cost'      => $this->cost_price = (string) $price->amount,
                default     => null,
            };
            $this->currency = $price->currency;
        }
    }

    protected function rules(): array
    {
        return [
            'sku'               => "required|string|max:100|unique:products,sku,{$this->productId}",
            'name_ru'           => 'required|string|max:255',
            'name_uz'           => 'nullable|string|max:255',
            'brand'             => 'nullable|string|max:100',
            'model_number'      => 'nullable|string|max:100',
            'description_ru'    => 'nullable|string',
            'category_id'       => 'required|exists:categories,id',
            'unit'              => 'required|string|max:50',
            'is_active'         => 'boolean',
            'is_visible_portal' => 'boolean',
            'is_serial'         => 'boolean',
            'retail_price'      => 'nullable|numeric|min:0',
            'wholesale_price'   => 'nullable|numeric|min:0',
            'cost_price'        => 'nullable|numeric|min:0',
            'currency'          => 'required|in:UZS,USD',
        ];
    }

    protected function messages(): array
    {
        return [
            'sku.required'         => 'Артикул обязателен.',
            'sku.unique'           => 'Такой артикул уже существует.',
            'name_ru.required'     => 'Название (рус) обязательно.',
            'category_id.required' => 'Выберите категорию.',
            'category_id.exists'   => 'Указанная категория не существует.',
        ];
    }

    public function save(): void
    {
        $this->authorize('update', $this->product);
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            $this->product->update([
                'sku'               => $data['sku'],
                'name_ru'           => $data['name_ru'],
                'name_uz'           => $data['name_uz'] ?? null,
                'brand'             => $data['brand'] ?? null,
                'model_number'      => $data['model_number'] ?? null,
                'description_ru'    => $data['description_ru'] ?? null,
                'category_id'       => $data['category_id'],
                'unit'              => $data['unit'],
                'is_active'         => $data['is_active'],
                'is_visible_portal' => $data['is_visible_portal'],
                'is_serial'         => $data['is_serial'],
            ]);

            // Sync prices: upsert retail / wholesale / cost
            $priceTypes = [
                'retail'    => $data['retail_price'],
                'wholesale' => $data['wholesale_price'],
                'cost'      => $data['cost_price'],
            ];

            foreach ($priceTypes as $type => $amount) {
                if ($amount !== '' && $amount !== null) {
                    $this->product->prices()->updateOrCreate(
                        ['type' => $type],
                        ['amount' => $amount, 'currency' => $data['currency'], 'is_active' => true]
                    );
                } else {
                    // Remove price if cleared
                    $this->product->prices()
                        ->where('type', $type)
                        ->delete();
                }
            }
        });

        session()->flash('success', 'Товар обновлён.');
        $this->dispatch('product-saved');
    }

    public function render()
    {
        return view('livewire.admin.catalog.products.edit-form', [
            'categories' => Category::where('is_active', true)->orderBy('name_ru')->get(),
        ]);
    }
}
