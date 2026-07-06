<?php

namespace Database\Factories;

use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = 1000000;
        $taxRate = 12;
        $taxAmount = round($subtotal * $taxRate / 100, 2);

        return [
            'number'        => 'INV-' . now()->year . '-' . fake()->unique()->numberBetween(1000, 999999),
            'quote_id'      => null,
            'customer_id'   => Customer::factory(),
            'manager_id'    => User::factory(),
            'currency'      => 'UZS',
            'exchange_rate' => 1,
            'status'        => 'draft',
            'due_date'      => now()->addDays(30)->toDateString(),
            'subtotal'      => $subtotal,
            'tax_rate'      => $taxRate,
            'tax_amount'    => $taxAmount,
            'total'         => $subtotal + $taxAmount,
            'paid_amount'   => 0,
            'notes'         => null,
        ];
    }
}
