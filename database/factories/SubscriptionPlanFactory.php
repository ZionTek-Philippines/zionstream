<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubscriptionPlanFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Basic', 'Standard', 'Premium', 'VIP']);

        return [
            'name'           => $name,
            'slug'           => Str::slug($name),
            'description'    => fake()->sentence(),
            'price'          => fake()->randomElement([99, 199, 499, 999]),
            'billing_period' => 'monthly',
            'features'       => ['Access to live streams', 'Chat access'],
            'is_active'      => true,
            'sort_order'     => 0,
        ];
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => ['billing_period' => 'yearly']);
    }
}
