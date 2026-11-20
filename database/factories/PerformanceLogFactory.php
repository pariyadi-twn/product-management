<?php

namespace Database\Factories;

use App\Models\PerformanceLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceLog>
 */
class PerformanceLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'endpoint' => '/products',
            'method' => 'GET',
            'duration_ms' => fake()->numberBetween(10, 900),
            'status_code' => 200,
            'result_count' => fake()->numberBetween(0, 50),
            'recorded_at' => now(),
        ];
    }

    public function slow(int $durationMs = 800): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_ms' => $durationMs,
        ]);
    }

    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_code' => 500,
        ]);
    }
}
