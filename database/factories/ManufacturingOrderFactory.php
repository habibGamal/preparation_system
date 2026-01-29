<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ManufacturingOrderStatus;
use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManufacturingOrder>
 */
final class ManufacturingOrderFactory extends Factory
{
    protected $model = ManufacturingOrder::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory()->manufacturedType(),
            'status' => ManufacturingOrderStatus::Draft,
            'output_quantity' => fake()->randomFloat(2, 1, 10),
            'notes' => fake()->optional()->sentence(),
            'completed_at' => null,
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->id,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ManufacturingOrderStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
