<?php

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StreamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'channel_id'         => Channel::factory(),
            'title'              => fake()->sentence(4),
            'description'        => fake()->paragraph(),
            'thumbnail'          => null,
            'agora_channel_name' => 'stream-' . Str::uuid(),
            'agora_uid'          => null,
            'status'             => 'scheduled',
            'claim_keywords'     => ['mine'],
            'peak_viewer_count'  => 0,
            'scheduled_at'       => now()->addHour(),
            'started_at'         => null,
            'ended_at'           => null,
        ];
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => 'live',
            'started_at' => now(),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => 'ended',
            'started_at' => now()->subHours(2),
            'ended_at'   => now()->subMinutes(30),
        ]);
    }
}
