<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ManufacturingRecipe;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManufacturingRecipe>
 */
final class ManufacturingRecipeFactory extends Factory
{
    protected $model = ManufacturingRecipe::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'product_id' => Product::factory()->manufacturedType(),
            'expected_output_quantity' => fake()->randomFloat(2, 1, 10),
            'notes' => fake()->optional()->sentence(),
            'is_auto_calculated' => false,
        ];
    }

    public function autoCalculated(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_auto_calculated' => true,
            'calculated_from_orders_count' => fake()->numberBetween(3, 20),
            'last_calculated_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->id,
        ]);
    }
}
