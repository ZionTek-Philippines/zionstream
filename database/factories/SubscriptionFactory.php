<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'plan_id'       => SubscriptionPlan::factory(),
            'status'        => 'active',
            'trial_ends_at' => null,
            'starts_at'     => now(),
            'ends_at'       => now()->addMonth(),
            'cancelled_at'  => null,
        ];
    }

    public function trialing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => 'trialing',
            'trial_ends_at' => now()->addDays(14),
            'starts_at'     => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
