<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ManufacturingRecipe;
use App\Models\ManufacturingRecipeItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManufacturingRecipeItem>
 */
final class ManufacturingRecipeItemFactory extends Factory
{
    protected $model = ManufacturingRecipeItem::class;

    public function definition(): array
    {
        return [
            'manufacturing_recipe_id' => ManufacturingRecipe::factory(),
            'product_id' => Product::factory()->rawType(),
            'quantity' => fake()->randomFloat(3, 0.1, 2),
            'usage_frequency' => 100,
        ];
    }

    public function forRecipe(ManufacturingRecipe $recipe): static
    {
        return $this->state(fn (array $attributes): array => [
            'manufacturing_recipe_id' => $recipe->id,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->id,
        ]);
    }

    public function optional(): static
    {
        return $this->state(fn (array $attributes): array => [
            'usage_frequency' => fake()->numberBetween(30, 69),
        ]);
    }
}
