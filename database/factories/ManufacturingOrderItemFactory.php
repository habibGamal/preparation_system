<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManufacturingOrderItem>
 */
final class ManufacturingOrderItemFactory extends Factory
{
    protected $model = ManufacturingOrderItem::class;

    public function definition(): array
    {
        return [
            'manufacturing_order_id' => ManufacturingOrder::factory(),
            'product_id' => Product::factory()->rawType(),
            'quantity' => fake()->randomFloat(2, 0.1, 5),
        ];
    }

    public function forOrder(ManufacturingOrder $order): static
    {
        return $this->state(fn (array $attributes): array => [
            'manufacturing_order_id' => $order->id,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->id,
        ]);
    }
}
