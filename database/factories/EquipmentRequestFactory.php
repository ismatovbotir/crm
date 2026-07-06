<?php

namespace Database\Factories;

use App\Models\Customer\Customer;
use App\Models\Support\EquipmentRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EquipmentRequest>
 */
class EquipmentRequestFactory extends Factory
{
    protected $model = EquipmentRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'manager_id'  => null,
            'subject'     => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'budget'      => fake()->randomFloat(2, 1000000, 20000000),
            'needed_by'   => now()->addDays(30)->toDateString(),
            'status'      => 'submitted',
            'notes'       => null,
        ];
    }
}
