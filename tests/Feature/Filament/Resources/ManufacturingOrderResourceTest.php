<?php

declare(strict_types=1);

use App\Enums\ManufacturingOrderStatus;
use App\Filament\Resources\ManufacturingOrderResource\Pages\CreateManufacturingOrder;
use App\Filament\Resources\ManufacturingOrderResource\Pages\EditManufacturingOrder;
use App\Filament\Resources\ManufacturingOrderResource\Pages\ListManufacturingOrders;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderItem;
use App\Models\Product;
use App\Services\ManufacturingOrderService;
use Filament\Actions\DeleteAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

it('can render the index page', function () {
    livewire(ListManufacturingOrders::class)
        ->assertOk();
});

it('can render the create page', function () {
    livewire(CreateManufacturingOrder::class)
        ->assertOk();
});

it('can render the edit page', function () {
    $order = ManufacturingOrder::factory()->create();

    livewire(EditManufacturingOrder::class, [
        'record' => $order->id,
    ])
        ->assertOk();
});

it('has column', function (string $column) {
    livewire(ListManufacturingOrders::class)
        ->assertTableColumnExists($column);
})->with(['id', 'product.name', 'status', 'output_quantity', 'items_count']);

it('can create a manufacturing order', function () {
    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $rawProduct = Product::factory()->rawType()->create();

    livewire(CreateManufacturingOrder::class)
        ->fillForm([
            'product_id' => $manufacturedProduct->id,
            'output_quantity' => 2.0,
            'notes' => 'Test order',
            'items' => [
                [
                    'product_id' => $rawProduct->id,
                    'quantity' => 1.0,
                ],
            ],
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(ManufacturingOrder::class, [
        'product_id' => $manufacturedProduct->id,
        'output_quantity' => 2.0,
        'status' => ManufacturingOrderStatus::Draft->value,
    ]);

    assertDatabaseHas(ManufacturingOrderItem::class, [
        'product_id' => $rawProduct->id,
        'quantity' => 1.0,
    ]);
});

it('can update a manufacturing order', function () {
    $order = ManufacturingOrder::factory()->create();

    livewire(EditManufacturingOrder::class, [
        'record' => $order->id,
    ])
        ->fillForm([
            'output_quantity' => 5.0,
            'notes' => 'Updated notes',
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(ManufacturingOrder::class, [
        'id' => $order->id,
        'output_quantity' => 5.0,
        'notes' => 'Updated notes',
    ]);
});

it('can delete a draft manufacturing order', function () {
    $order = ManufacturingOrder::factory()->create();

    livewire(EditManufacturingOrder::class, [
        'record' => $order->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing($order);
});

it('validates required fields', function (array $data, array $errors) {
    $manufacturedProduct = Product::factory()->manufacturedType()->create();

    livewire(CreateManufacturingOrder::class)
        ->fillForm([
            'product_id' => $manufacturedProduct->id,
            'output_quantity' => 1,
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`product_id` is required' => [['product_id' => null], ['product_id' => 'required']],
    '`output_quantity` is required' => [['output_quantity' => null], ['output_quantity' => 'required']],
]);

it('completes manufacturing order and updates inventory', function () {
    // Create raw material with inventory
    $rawProduct = Product::factory()->rawType()->create();
    $rawProduct->inventory->update(['quantity' => 10.0]);

    // Create manufactured product
    $manufacturedProduct = Product::factory()->manufacturedType()->create();
    $manufacturedProduct->inventory->update(['quantity' => 0]);

    // Create manufacturing order
    $order = ManufacturingOrder::factory()
        ->forProduct($manufacturedProduct)
        ->create(['output_quantity' => 2.0]);

    ManufacturingOrderItem::factory()
        ->forOrder($order)
        ->forProduct($rawProduct)
        ->create(['quantity' => 3.0]);

    // Complete the order
    $service = app(ManufacturingOrderService::class);
    $service->complete($order);

    // Verify inventory changes
    $rawProduct->refresh();
    $manufacturedProduct->refresh();

    expect($rawProduct->inventory->quantity)->toBe('7.00'); // 10 - 3
    expect($manufacturedProduct->inventory->quantity)->toBe('2.00'); // 0 + 2
    expect($order->fresh()->status)->toBe(ManufacturingOrderStatus::Completed);
    expect($order->fresh()->completed_at)->not->toBeNull();
});

it('can clone a manufacturing order', function () {
    $order = ManufacturingOrder::factory()->completed()->create();
    ManufacturingOrderItem::factory()
        ->forOrder($order)
        ->count(2)
        ->create();

    $service = app(ManufacturingOrderService::class);
    $clonedOrder = $service->clone($order);

    expect($clonedOrder->status)->toBe(ManufacturingOrderStatus::Draft);
    expect($clonedOrder->completed_at)->toBeNull();
    expect($clonedOrder->items)->toHaveCount(2);
    expect($clonedOrder->product_id)->toBe($order->product_id);
});
