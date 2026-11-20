<?php

namespace Database\Factories;

use App\Models\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => Alert::TYPE_SLOW_SEARCH,
            'severity' => Alert::SEVERITY_WARNING,
            'message' => 'Pencarian membutuhkan waktu lebih dari ambang batas.',
            'metric_value' => 800,
            'threshold' => 500,
            'is_resolved' => false,
            'triggered_at' => now(),
            'resolved_at' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);
    }
}
