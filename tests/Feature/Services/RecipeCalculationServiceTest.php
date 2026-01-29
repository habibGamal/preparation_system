<?php

declare(strict_types=1);

use App\Enums\SettingKey;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderItem;
use App\Models\ManufacturingRecipe;
use App\Models\Product;
use App\Models\Setting;
use App\Services\RecipeCalculationService;

beforeEach(function () {
    $this->service = app(RecipeCalculationService::class);
});

it('calculates recipe from completed orders using consumption rates', function () {
    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $rawProduct1 = Product::factory()->rawType()->create();
    $rawProduct2 = Product::factory()->rawType()->create();

    // Create 3 completed orders with similar consumption rates
    for ($i = 0; $i < 3; $i++) {
        $order = ManufacturingOrder::factory()
            ->forProduct($manufacturedProduct)
            ->completed()
            ->create(['output_quantity' => 2.0]);

        // Raw 1: 3.0 qty / 2.0 output = 1.5 consumption rate
        ManufacturingOrderItem::factory()
            ->forOrder($order)
            ->forProduct($rawProduct1)
            ->create(['quantity' => 3.0]);

        // Raw 2: 1.0 qty / 2.0 output = 0.5 consumption rate
        ManufacturingOrderItem::factory()
            ->forOrder($order)
            ->forProduct($rawProduct2)
            ->create(['quantity' => 1.0]);
    }

    $recipe = $this->service->calculateRecipeFromOrders($manufacturedProduct);

    expect($recipe)->not->toBeNull();
    expect($recipe->is_auto_calculated)->toBeTrue();
    expect($recipe->calculated_from_orders_count)->toBe(3);
    expect($recipe->items)->toHaveCount(2);

    // Verify consumption rates (quantity is rate per unit output)
    $item1 = $recipe->items->firstWhere('product_id', $rawProduct1->id);
    $item2 = $recipe->items->firstWhere('product_id', $rawProduct2->id);

    expect((float) $item1->quantity)->toBe(1.5); // avg consumption rate
    expect((float) $item2->quantity)->toBe(0.5); // avg consumption rate
    expect((float) $item1->usage_frequency)->toBe(100.0); // used in all orders
    expect((float) $item2->usage_frequency)->toBe(100.0);
});

it('does not calculate recipe when insufficient orders', function () {
    Setting::set(SettingKey::MinimumOrdersForRecipe, '3');

    $manufacturedProduct = Product::factory()->manufacturedType()->create();

    // Create only 2 orders (less than minimum)
    ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->completed()
        ->count(2)
        ->create();

    $recipe = $this->service->calculateRecipeFromOrders($manufacturedProduct);

    expect($recipe)->toBeNull();
});

it('only uses most recent orders up to maximum limit', function () {
    Setting::set(SettingKey::MaximumOrdersForRecipe, '3');

    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $rawProduct = Product::factory()->rawType()->create();

    // Create 5 completed orders (only most recent 3 should be used)
    for ($i = 0; $i < 5; $i++) {
        $order = ManufacturingOrder::factory()
            ->forProduct($manufacturedProduct)
            ->completed()
            ->create([
                'completed_at' => now()->subDays($i),
                'output_quantity' => 2.0,
            ]);

        ManufacturingOrderItem::factory()
            ->forOrder($order)
            ->forProduct($rawProduct)
            ->create(['quantity' => 3.0]);
    }

    $recipe = $this->service->calculateRecipeFromOrders($manufacturedProduct);

    expect($recipe)->not->toBeNull();
    expect($recipe->calculated_from_orders_count)->toBe(3); // Only most recent 3 orders
});

it('updates existing recipe when recalculating', function () {
    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $rawProduct = Product::factory()->rawType()->create();

    // Create initial recipe
    $existingRecipe = ManufacturingRecipe::factory()
        ->forProduct($manufacturedProduct)
        ->autoCalculated()
        ->create();

    // Create new completed orders
    for ($i = 0; $i < 3; $i++) {
        $order = ManufacturingOrder::factory()
            ->forProduct($manufacturedProduct)
            ->completed()
            ->create(['output_quantity' => 2.0]);

        ManufacturingOrderItem::factory()
            ->forOrder($order)
            ->forProduct($rawProduct)
            ->create(['quantity' => 3.0]);
    }

    $recipe = $this->service->calculateRecipeFromOrders($manufacturedProduct);

    // Should be same record, updated
    expect($recipe->id)->toBe($existingRecipe->id);
});

it('detects variance from recipe comparing actual vs expected consumption', function () {
    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $rawProduct = Product::factory()->rawType()->create();

    // Create recipe with consumption rate of 1.5 per unit
    $recipe = ManufacturingRecipe::factory()
        ->forProduct($manufacturedProduct)
        ->autoCalculated()
        ->create();

    $recipe->items()->create([
        'product_id' => $rawProduct->id,
        'quantity' => 1.5, // consumption rate per unit
        'usage_frequency' => 100,
    ]);

    // Create order with variance: output 2, raw used 5 (rate=2.5, expected=3.0)
    // Expected = 1.5 * 2 = 3.0, Actual = 5.0
    // Variance = (5.0 - 3.0) / 3.0 * 100 = 66.67%
    $order = ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->create(['output_quantity' => 2.0]);

    ManufacturingOrderItem::factory()
        ->forOrder($order)
        ->forProduct($rawProduct)
        ->create(['quantity' => 5.0]);

    $warnings = $this->service->getVarianceFromRecipe($order);

    expect($warnings)->toHaveCount(1);
    expect($warnings[0]['type'])->toBe('raw_material');
    expect($warnings[0]['variance'])->toBeGreaterThan(50); // ~66.67%
});

it('returns empty array when no recipe exists for variance check', function () {
    $manufacturedProduct = Product::factory()->manufacturedType()->create();

    $order = ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->create();

    $warnings = $this->service->getVarianceFromRecipe($order);

    expect($warnings)->toBeArray();
    expect($warnings)->toBeEmpty();
});

it('checks if enough orders exist for recipe calculation', function () {
    Setting::set(SettingKey::MinimumOrdersForRecipe, '3');

    $manufacturedProduct = Product::factory()->manufacturedType()->create();

    // Create 2 orders (insufficient)
    ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->completed()
        ->count(2)
        ->create();

    expect($this->service->hasEnoughOrdersForRecipe($manufacturedProduct))->toBeFalse();

    // Add one more order (now sufficient)
    ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->completed()
        ->create();

    expect($this->service->hasEnoughOrdersForRecipe($manufacturedProduct))->toBeTrue();
});

it('checks if maximum order limit is reached', function () {
    Setting::set(SettingKey::MaximumOrdersForRecipe, '10');

    $manufacturedProduct = Product::factory()->manufacturedType()->create();

    // Create 9 orders (below maximum)
    ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->completed()
        ->count(9)
        ->create();

    expect($this->service->hasReachedMaximumOrders($manufacturedProduct))->toBeFalse();

    // Add one more order (reaches maximum)
    ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->completed()
        ->create();

    expect($this->service->hasReachedMaximumOrders($manufacturedProduct))->toBeTrue();
});

it('generates recipe name from product name', function () {
    $manufacturedProduct = Product::factory()->manufacturedType()->create(['name' => 'كيكة']);
    $rawProduct = Product::factory()->rawType()->create();

    for ($i = 0; $i < 3; $i++) {
        $order = ManufacturingOrder::factory()
            ->forProduct($manufacturedProduct)
            ->completed()
            ->create();

        ManufacturingOrderItem::factory()
            ->forOrder($order)
            ->forProduct($rawProduct)
            ->create();
    }

    $recipe = $this->service->calculateRecipeFromOrders($manufacturedProduct);

    expect($recipe->name)->toContain('كيكة');
});

it('tracks usage frequency for optional ingredients', function () {
    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $alwaysUsed = Product::factory()->rawType()->create();
    $sometimesUsed = Product::factory()->rawType()->create();

    // Create 5 orders, always use first ingredient, only use second in 2
    for ($i = 0; $i < 5; $i++) {
        $order = ManufacturingOrder::factory()
            ->forProduct($manufacturedProduct)
            ->completed()
            ->create(['output_quantity' => 1.0]);

        ManufacturingOrderItem::factory()
            ->forOrder($order)
            ->forProduct($alwaysUsed)
            ->create(['quantity' => 2.0]);

        if ($i < 2) {
            ManufacturingOrderItem::factory()
                ->forOrder($order)
                ->forProduct($sometimesUsed)
                ->create(['quantity' => 1.0]);
        }
    }

    $recipe = $this->service->calculateRecipeFromOrders($manufacturedProduct);

    $alwaysItem = $recipe->items->firstWhere('product_id', $alwaysUsed->id);
    $sometimesItem = $recipe->items->firstWhere('product_id', $sometimesUsed->id);

    expect((float) $alwaysItem->usage_frequency)->toBe(100.0); // 5/5 = 100%
    expect((float) $sometimesItem->usage_frequency)->toBe(40.0); // 2/5 = 40%
});

it('excludes rarely used ingredients below threshold', function () {
    Setting::set(SettingKey::IncludeIngredientThreshold, '30');

    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $commonIngredient = Product::factory()->rawType()->create();
    $rareIngredient = Product::factory()->rawType()->create();

    // Create 5 orders, use rare ingredient only once (20%)
    for ($i = 0; $i < 5; $i++) {
        $order = ManufacturingOrder::factory()
            ->forProduct($manufacturedProduct)
            ->completed()
            ->create(['output_quantity' => 1.0]);

        ManufacturingOrderItem::factory()
            ->forOrder($order)
            ->forProduct($commonIngredient)
            ->create(['quantity' => 2.0]);

        if ($i === 0) {
            ManufacturingOrderItem::factory()
                ->forOrder($order)
                ->forProduct($rareIngredient)
                ->create(['quantity' => 1.0]);
        }
    }

    $recipe = $this->service->calculateRecipeFromOrders($manufacturedProduct);

    // Only common ingredient should be in recipe
    expect($recipe->items)->toHaveCount(1);
    expect($recipe->items->first()->product_id)->toBe($commonIngredient->id);
});

it('warns about missing required ingredients in order', function () {
    Setting::set(SettingKey::RequiredIngredientThreshold, '70');

    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $requiredIngredient = Product::factory()->rawType()->create();

    // Create recipe with required ingredient
    $recipe = ManufacturingRecipe::factory()
        ->forProduct($manufacturedProduct)
        ->autoCalculated()
        ->create();

    $recipe->items()->create([
        'product_id' => $requiredIngredient->id,
        'quantity' => 1.0,
        'usage_frequency' => 100, // Required ingredient
    ]);

    // Create order WITHOUT the required ingredient
    $order = ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->create(['output_quantity' => 1.0]);

    $warnings = $this->service->getVarianceFromRecipe($order);

    // Should warn about missing ingredient
    expect($warnings)->toHaveCount(1);
    expect($warnings[0]['type'])->toBe('missing_ingredient');
});
