<?php

namespace Database\Factories;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'             => fake()->unique()->company(),
            'legal_name'       => 'OOO '.fake()->company(),
            'inn'              => fake()->unique()->numerify('##########'),
            'oked'             => fake()->numerify('#####'),
            'business_type_id' => null,
            'segment'          => fake()->randomElement(['A', 'B', 'C']),
            'status'           => 'active',
            'region'           => 'Ташкент',
            'city'             => 'Ташкент',
            'address'          => fake()->address(),
            'phone'            => fake()->numerify('+998#########'),
            'email'            => fake()->unique()->safeEmail(),
            'website'          => null,
            'bank_id'          => null,
            'bank_account'     => fake()->numerify('####################'),
            'credit_limit'     => null,
            'payment_terms_days' => 30,
            'customer_since'   => now()->toDateString(),
            'notes'            => null,
        ];
    }
}
