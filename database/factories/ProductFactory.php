<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductType;
use App\Enums\ProductUnit;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'category_id' => Category::factory(),
            'barcode' => fake()->unique()->ean13(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'cost' => fake()->randomFloat(2, 5, 800),
            'min_stock' => fake()->numberBetween(10, 100),
            'avg_purchase_quantity' => fake()->numberBetween(50, 500),
            'type' => fake()->randomElement(ProductType::cases()),
            'unit' => fake()->randomElement(ProductUnit::cases()),
        ];
    }

    public function rawType(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ProductType::Raw,
        ]);
    }

    public function manufacturedType(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => ProductType::Manufactured,
        ]);
    }
}
