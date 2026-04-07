<?php

declare(strict_types=1);

use App\Filament\Pages\ConsumerOutReport;
use App\Models\Consumer;
use App\Models\ManufacturedMaterialOut;
use App\Models\ManufacturedMaterialOutItem;
use App\Models\Product;
use App\Models\RawMaterialOut;
use App\Models\RawMaterialOutItem;
use App\Models\User;
use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->create());
});

it('can render the consumer out report page', function (): void {
    livewire(ConsumerOutReport::class)
        ->assertOk();
});

it('shows only selected consumer raw out items in chosen day with aggregation by product', function (): void {
    $user = User::query()->firstOrFail();
    $consumer = Consumer::factory()->create();
    $otherConsumer = Consumer::factory()->create();
    $rawProduct = Product::factory()->rawType()->create();

    $outForConsumer = RawMaterialOut::factory()->closed()->create([
        'user_id' => $user->id,
        'consumer_id' => $consumer->id,
        'closed_at' => Carbon::parse('2026-03-20 09:00:00'),
    ]);
    $itemForConsumerFirst = RawMaterialOutItem::create([
        'raw_material_out_id' => $outForConsumer->id,
        'product_id' => $rawProduct->id,
        'quantity' => 5,
        'price' => 2,
        'total' => 10,
    ]);
    $itemForConsumerSecond = RawMaterialOutItem::create([
        'raw_material_out_id' => $outForConsumer->id,
        'product_id' => $rawProduct->id,
        'quantity' => 7,
        'price' => 4,
        'total' => 28,
    ]);

    $outForOtherConsumer = RawMaterialOut::factory()->closed()->create([
        'user_id' => $user->id,
        'consumer_id' => $otherConsumer->id,
        'closed_at' => Carbon::parse('2026-03-20 10:00:00'),
    ]);
    $itemForOtherConsumer = RawMaterialOutItem::create([
        'raw_material_out_id' => $outForOtherConsumer->id,
        'product_id' => $rawProduct->id,
        'quantity' => 6,
        'price' => 2,
        'total' => 12,
    ]);

    livewire(ConsumerOutReport::class)
        ->fillForm([
            'consumer_id' => $consumer->id,
            'mode' => 'single',
            'single_date' => '2026-03-20',
        ])
        ->call('generateReport')
        ->loadTable()
        ->assertCanSeeTableRecords([$itemForConsumerFirst])
        ->assertCanNotSeeTableRecords([$itemForConsumerSecond])
        ->assertCanNotSeeTableRecords([$itemForOtherConsumer]);
});

it('shows only selected consumer manufactured out items in chosen range with aggregation by product', function (): void {
    $user = User::query()->firstOrFail();
    $consumer = Consumer::factory()->create();
    $otherConsumer = Consumer::factory()->create();
    $manufacturedProduct = Product::factory()->manufacturedType()->create();

    $inRangeOut = ManufacturedMaterialOut::factory()->closed()->create([
        'user_id' => $user->id,
        'consumer_id' => $consumer->id,
        'closed_at' => Carbon::parse('2026-03-21 09:00:00'),
    ]);
    $inRangeItemFirst = ManufacturedMaterialOutItem::create([
        'manufactured_material_out_id' => $inRangeOut->id,
        'product_id' => $manufacturedProduct->id,
        'quantity' => 7,
        'price' => 3,
        'total' => 21,
    ]);
    $inRangeItemSecond = ManufacturedMaterialOutItem::create([
        'manufactured_material_out_id' => $inRangeOut->id,
        'product_id' => $manufacturedProduct->id,
        'quantity' => 5,
        'price' => 5,
        'total' => 25,
    ]);

    $outOfRangeOut = ManufacturedMaterialOut::factory()->closed()->create([
        'user_id' => $user->id,
        'consumer_id' => $consumer->id,
        'closed_at' => Carbon::parse('2026-03-25 09:00:00'),
    ]);
    $outOfRangeItem = ManufacturedMaterialOutItem::create([
        'manufactured_material_out_id' => $outOfRangeOut->id,
        'product_id' => $manufacturedProduct->id,
        'quantity' => 4,
        'price' => 3,
        'total' => 12,
    ]);

    $otherConsumerOut = ManufacturedMaterialOut::factory()->closed()->create([
        'user_id' => $user->id,
        'consumer_id' => $otherConsumer->id,
        'closed_at' => Carbon::parse('2026-03-21 12:00:00'),
    ]);
    $otherConsumerItem = ManufacturedMaterialOutItem::create([
        'manufactured_material_out_id' => $otherConsumerOut->id,
        'product_id' => $manufacturedProduct->id,
        'quantity' => 8,
        'price' => 3,
        'total' => 24,
    ]);

    livewire(ConsumerOutReport::class)
        ->set('activeTab', 'manufactured')
        ->fillForm([
            'consumer_id' => $consumer->id,
            'mode' => 'range',
            'start_date' => '2026-03-20',
            'end_date' => '2026-03-22',
        ])
        ->call('generateReport')
        ->loadTable()
        ->assertCanSeeTableRecords([$inRangeItemFirst])
        ->assertCanNotSeeTableRecords([$inRangeItemSecond])
        ->assertCanNotSeeTableRecords([$outOfRangeItem])
        ->assertCanNotSeeTableRecords([$otherConsumerItem]);
});
