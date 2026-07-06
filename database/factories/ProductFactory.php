<?php

namespace Database\Factories;

use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Category model does not use the HasFactory trait / newFactory() override
            // (unlike every other model with a factory in this project — see CLAUDE.md
            // §2.4 convention and CategoryFactory), so `Category::factory()` is not
            // available here. Falling back to instantiating CategoryFactory directly.
            'category_id'        => CategoryFactory::new(),
            'sku'                => 'SKU-' . fake()->unique()->numberBetween(100000, 999999),
            'name_ru'            => fake()->words(3, true),
            'name_uz'            => null,
            'description_ru'     => fake()->sentence(),
            'description_uz'     => null,
            'brand'              => fake()->company(),
            'model_number'       => fake()->bothify('MX-####'),
            'unit'               => 'шт',
            'is_active'          => true,
            'is_visible_portal'  => true,
            'is_serial'          => false,
            'notes'              => null,
        ];
    }
}
