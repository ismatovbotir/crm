<?php

namespace Database\Factories;

use App\Models\Support\Ticket;
use App\Models\Support\TicketComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketComment>
 */
class TicketCommentFactory extends Factory
{
    protected $model = TicketComment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id'   => Ticket::factory(),
            'user_id'     => User::factory(),
            'body'        => fake()->sentence(10),
            'is_internal' => false,
        ];
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => ['is_internal' => true]);
    }
}
