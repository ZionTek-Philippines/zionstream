<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id'  => User::factory(),
            'moderator_id' => null,
            'status'       => 'pending',
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderator_id' => User::factory(),
            'status'       => 'open',
        ]);
    }
}
