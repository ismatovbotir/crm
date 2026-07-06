<?php

namespace Database\Factories;

use App\Models\Catalog\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name_ru'    => ucfirst($name),
            'name_uz'    => null,
            'slug'       => \Illuminate\Support\Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 999999),
            'parent_id'  => null,
            'group_id'   => null,
            'icon'       => null,
            'sort_order' => 0,
            'is_active'  => true,
        ];
    }
}
