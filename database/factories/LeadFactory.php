<?php

namespace Database\Factories;

use App\Models\Lead\Lead;
use App\Models\Lead\LeadSource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'             => fake()->name(),
            'company'          => fake()->company(),
            'customer_id'      => null,
            'phone'            => fake()->numerify('+998#########'),
            'email'            => fake()->unique()->safeEmail(),
            'source_id'        => LeadSource::firstOrCreate(
                ['slug' => 'site'],
                ['name' => 'Сайт rsg.uz', 'is_active' => true, 'sort_order' => 1]
            )->id,
            'manager_id'       => User::factory(),
            'created_by'       => null,
            'status'           => 'new',
            'score'            => fake()->numberBetween(1, 10),
            'budget'           => fake()->randomFloat(2, 1000000, 50000000),
            'business_type_id' => null,
            'region'           => 'Ташкент',
            'notes'            => fake()->sentence(),
        ];
    }
}
