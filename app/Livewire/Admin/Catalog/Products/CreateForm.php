<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductStock;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateForm extends Component
{
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

    protected function rules(): array
    {
        return [
            'sku'              => 'required|string|max:100|unique:products,sku',
            'name_ru'          => 'required|string|max:255',
            'name_uz'          => 'nullable|string|max:255',
            'brand'            => 'nullable|string|max:100',
            'model_number'     => 'nullable|string|max:100',
            'description_ru'   => 'nullable|string',
            'category_id'      => 'required|exists:categories,id',
            'unit'             => 'required|string|max:50',
            'is_active'        => 'boolean',
            'is_visible_portal' => 'boolean',
            'is_serial'        => 'boolean',
            'retail_price'     => 'nullable|numeric|min:0',
            'wholesale_price'  => 'nullable|numeric|min:0',
            'cost_price'       => 'nullable|numeric|min:0',
            'currency'         => 'required|in:UZS,USD',
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
        $this->authorize('create', Product::class);
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            $product = Product::create([
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

            // Create price records for each type that was provided
            $priceTypes = [
                'retail'    => $data['retail_price'],
                'wholesale' => $data['wholesale_price'],
                'cost'      => $data['cost_price'],
            ];

            foreach ($priceTypes as $type => $amount) {
                if ($amount !== '' && $amount !== null) {
                    $product->prices()->create([
                        'type'      => $type,
                        'amount'    => $amount,
                        'currency'  => $data['currency'],
                        'is_active' => true,
                    ]);
                }
            }

            // Create default stock record for main warehouse
            ProductStock::create([
                'product_id' => $product->id,
                'warehouse'  => 'main',
                'quantity'   => 0,
                'reserved'   => 0,
            ]);
        });

        session()->flash('success', 'Товар создан.');
        $this->dispatch('product-saved');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.admin.catalog.products.create-form', [
            'categories' => Category::where('is_active', true)->orderBy('name_ru')->get(),
        ]);
    }
}
