<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'sku'            => strtoupper(Str::random(3)) . '-' . fake()->unique()->randomNumber(5),
            'name'           => $name,
            'slug'           => Str::slug($name) . '-' . fake()->unique()->randomNumber(4),
            'description'    => fake()->paragraphs(2, true),
            'price'          => fake()->randomFloat(2, 500, 500000),
            'images'         => [],
            'stock_quantity' => fake()->numberBetween(1, 20),
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
