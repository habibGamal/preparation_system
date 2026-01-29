<?php

declare(strict_types=1);

use App\Filament\Resources\ManufacturingOrderResource\Pages\ViewManufacturingOrder;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderItem;
use App\Models\ManufacturingRecipe;
use App\Models\Product;

use function Pest\Livewire\livewire;

it('can render the view page', function () {
    $order = ManufacturingOrder::factory()
        ->completed()
        ->create();

    livewire(ViewManufacturingOrder::class, [
        'record' => $order->id,
    ])
        ->assertOk();
});

it('displays order information', function () {
    $order = ManufacturingOrder::factory()
        ->completed()
        ->create(['output_quantity' => 5.0]);

    livewire(ViewManufacturingOrder::class, [
        'record' => $order->id,
    ])
        ->assertSeeText($order->product->name)
        ->assertSeeText('٥٫٠٠'); // Arabic numerals
});

it('displays raw materials used', function () {
    $order = ManufacturingOrder::factory()
        ->completed()
        ->create();

    $rawProduct = Product::factory()->rawType()->create();
    ManufacturingOrderItem::factory()
        ->forOrder($order)
        ->forProduct($rawProduct)
        ->create(['quantity' => 3.0]);

    livewire(ViewManufacturingOrder::class, [
        'record' => $order->id,
    ])
        ->assertSeeText($rawProduct->name)
        ->assertSeeText('٣٫٠٠'); // Arabic numerals
});

it('displays expected quantity and variance when recipe exists', function () {
    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $rawProduct = Product::factory()->rawType()->create();

    // Create recipe with consumption rate of 1.5 per unit
    $recipe = ManufacturingRecipe::factory()
        ->forProduct($manufacturedProduct)
        ->autoCalculated()
        ->create();

    $recipe->items()->create([
        'product_id' => $rawProduct->id,
        'quantity' => 1.5, // consumption rate
        'usage_frequency' => 100,
    ]);

    // Create order: output 2, expected = 1.5 * 2 = 3.0, actual = 3.0
    $order = ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->completed()
        ->create(['output_quantity' => 2.0]);

    ManufacturingOrderItem::factory()
        ->forOrder($order)
        ->forProduct($rawProduct)
        ->create(['quantity' => 3.0]);

    livewire(ViewManufacturingOrder::class, [
        'record' => $order->id,
    ])
        ->assertSeeText($rawProduct->name)
        ->assertSeeText('٣٫٠٠') // Expected quantity in Arabic numerals
        ->assertOk();
});

it('displays variance warnings when variance exceeds threshold', function () {
    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $rawProduct = Product::factory()->rawType()->create();

    // Create recipe
    $recipe = ManufacturingRecipe::factory()
        ->forProduct($manufacturedProduct)
        ->autoCalculated()
        ->create();

    $recipe->items()->create([
        'product_id' => $rawProduct->id,
        'quantity' => 1.5,
        'usage_frequency' => 100,
    ]);

    // Create order with high variance: expected = 3.0, actual = 5.0 (66% variance)
    $order = ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->completed()
        ->create(['output_quantity' => 2.0]);

    ManufacturingOrderItem::factory()
        ->forOrder($order)
        ->forProduct($rawProduct)
        ->create(['quantity' => 5.0]);

    livewire(ViewManufacturingOrder::class, [
        'record' => $order->id,
    ])
        ->assertSeeText('تحذيرات الانحراف')
        ->assertSeeText($rawProduct->name);
});

it('hides variance warnings section when no warnings exist', function () {
    $order = ManufacturingOrder::factory()
        ->completed()
        ->create();

    $rawProduct = Product::factory()->rawType()->create();
    ManufacturingOrderItem::factory()
        ->forOrder($order)
        ->forProduct($rawProduct)
        ->create();

    // No recipe exists, so no warnings
    livewire(ViewManufacturingOrder::class, [
        'record' => $order->id,
    ])
        ->assertDontSeeText('تحذيرات الانحراف');
});
