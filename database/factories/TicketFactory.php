<?php

namespace Database\Factories;

use App\Models\Customer\Customer;
use App\Models\Support\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number'      => 'T-' . fake()->unique()->numberBetween(10000, 99999),
            'customer_id' => Customer::factory(),
            'contact_id'  => null,
            'category_id' => null,
            'assignee_id' => null,
            'created_by'  => User::factory(),
            'serial_id'   => null,
            // Real column default (see migration 2026_04_28_160100_create_tickets_table)
            // is 'medium', not 'normal' as documented in data-contracts.md.
            'priority'    => 'medium',
            'status'      => 'open',
            'subject'     => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'resolved_at' => null,
            'closed_at'   => null,
            'csat_score'  => null,
        ];
    }

    public function withPriority(string $priority): static
    {
        return $this->state(fn (array $attributes) => ['priority' => $priority]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => 'closed',
            'resolved_at' => now()->subHour(),
            'closed_at'   => now(),
        ]);
    }
}
