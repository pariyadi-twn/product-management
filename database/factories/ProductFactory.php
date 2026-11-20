<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(5000, 5000000);

        return [
            'sku' => 'SKU-'.fake()->unique()->numerify('######'),
            'name' => fake()->unique()->words(3, true),
            'category_id' => Category::factory(),
            'price' => $price,
            'stock' => fake()->numberBetween(0, 200),
            'status' => fake()->randomElement([Product::STATUS_AKTIF, Product::STATUS_AKTIF, Product::STATUS_NONAKTIF]),
            'description' => fake()->optional()->paragraph(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Product::STATUS_NONAKTIF,
        ]);
    }

    public function habis(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    public function menipis(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => fake()->numberBetween(1, 10),
        ]);
    }
}
