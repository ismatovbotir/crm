<?php

namespace Database\Factories;

use App\Models\Customer\Customer;
use App\Models\Quote\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = 1000000;
        $vatPercent = 12;
        $vatAmount = round($subtotal * $vatPercent / 100, 2);

        return [
            'number'           => 'КП-' . now()->year . '-' . fake()->unique()->numberBetween(1000, 999999),
            'customer_id'      => Customer::factory(),
            'manager_id'       => User::factory(),
            'contact_id'       => null,
            'currency'         => 'UZS',
            'exchange_rate'    => 1,
            'issue_date'       => now()->toDateString(),
            'status'           => 'draft',
            'valid_until'      => now()->addDays(14)->toDateString(),
            'subtotal'         => $subtotal,
            'discount_percent' => 0,
            'discount_total'   => 0,
            'vat_percent'      => $vatPercent,
            'vat_amount'       => $vatAmount,
            'total'            => $subtotal + $vatAmount,
            'version'          => 1,
            'notes'            => null,
            'terms'            => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => 'accepted',
            'sent_at'     => now()->subDays(3),
            'viewed_at'   => now()->subDays(2),
            'accepted_at' => now()->subDay(),
        ]);
    }
}
