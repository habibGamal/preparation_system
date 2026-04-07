<?php

declare(strict_types=1);

use App\Enums\MaterialEntranceStatus;
use App\Enums\ProductType;
use App\Filament\Pages\RawMaterialReport;
use App\Models\Product;
use App\Models\RawMaterialEntrance;
use App\Models\RawMaterialEntranceItem;
use App\Models\RawMaterialOut;
use App\Models\RawMaterialOutItem;
use App\Models\Stocktaking;
use App\Models\StocktakingItem;
use App\Models\User;
use App\Models\Waste;
use App\Models\WastedItem;
use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->create());
});

it('can render the raw material report page', function (): void {
    livewire(RawMaterialReport::class)
        ->assertOk();
});

it('calculates single day report values', function (): void {
    $user = User::query()->firstOrFail();
    $rawProduct = Product::factory()->rawType()->create();
    $manufacturedProduct = Product::factory()->manufacturedType()->create();

    $entranceBefore = RawMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-09 20:00:00'),
    ]);
    RawMaterialEntranceItem::create([
        'raw_material_entrance_id' => $entranceBefore->id,
        'product_id' => $rawProduct->id,
        'quantity' => 10,
        'price' => 1,
        'total' => 10,
    ]);

    $outBefore = RawMaterialOut::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-09 21:00:00'),
    ]);
    RawMaterialOutItem::create([
        'raw_material_out_id' => $outBefore->id,
        'product_id' => $rawProduct->id,
        'quantity' => 4,
        'price' => 1,
        'total' => 4,
    ]);

    $stocktakingBefore = Stocktaking::create([
        'user_id' => $user->id,
        'product_type' => ProductType::Raw,
        'total' => 0,
        'closed_at' => Carbon::parse('2026-02-09 22:00:00'),
    ]);
    StocktakingItem::create([
        'stocktaking_id' => $stocktakingBefore->id,
        'product_id' => $rawProduct->id,
        'stock_quantity' => 5,
        'real_quantity' => 7,
        'price' => 1,
        'total' => 2,
    ]);

    $wasteBefore = Waste::create([
        'user_id' => $user->id,
        'type' => ProductType::Raw,
        'total' => 0,
        'closed_at' => Carbon::parse('2026-02-09 23:00:00'),
    ]);
    WastedItem::create([
        'waste_id' => $wasteBefore->id,
        'product_id' => $rawProduct->id,
        'quantity' => 1,
        'price' => 1,
        'total' => 1,
    ]);

    $entranceDuring = RawMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-10 10:00:00'),
        'status' => MaterialEntranceStatus::Closed,
    ]);
    RawMaterialEntranceItem::create([
        'raw_material_entrance_id' => $entranceDuring->id,
        'product_id' => $rawProduct->id,
        'quantity' => 8,
        'price' => 1,
        'total' => 8,
    ]);

    $manufacturedEntranceDuring = RawMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-10 10:30:00'),
        'status' => MaterialEntranceStatus::Closed,
    ]);
    RawMaterialEntranceItem::create([
        'raw_material_entrance_id' => $manufacturedEntranceDuring->id,
        'product_id' => $manufacturedProduct->id,
        'quantity' => 50,
        'price' => 1,
        'total' => 50,
    ]);

    $outDuring = RawMaterialOut::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-10 12:00:00'),
        'status' => MaterialEntranceStatus::Closed,
    ]);
    RawMaterialOutItem::create([
        'raw_material_out_id' => $outDuring->id,
        'product_id' => $rawProduct->id,
        'quantity' => 3,
        'price' => 1,
        'total' => 3,
    ]);

    $stocktakingDuring = Stocktaking::create([
        'user_id' => $user->id,
        'product_type' => ProductType::Raw,
        'total' => 0,
        'closed_at' => Carbon::parse('2026-02-10 14:00:00'),
    ]);
    StocktakingItem::create([
        'stocktaking_id' => $stocktakingDuring->id,
        'product_id' => $rawProduct->id,
        'stock_quantity' => 10,
        'real_quantity' => 9,
        'price' => 1,
        'total' => -1,
    ]);

    $wasteDuring = Waste::create([
        'user_id' => $user->id,
        'type' => ProductType::Raw,
        'total' => 0,
        'closed_at' => Carbon::parse('2026-02-10 15:00:00'),
    ]);
    WastedItem::create([
        'waste_id' => $wasteDuring->id,
        'product_id' => $rawProduct->id,
        'quantity' => 2,
        'price' => 1,
        'total' => 2,
    ]);

    $component = livewire(RawMaterialReport::class)
        ->fillForm([
            'mode' => 'single',
            'single_date' => '2026-02-10',
        ])
        ->call('generateReport')
        ->loadTable()
        ->assertCanSeeTableRecords([$rawProduct])
        ->assertCanNotSeeTableRecords([$manufacturedProduct])
        ->assertHasNoFormErrors();

    expect($component->instance()->report)->toMatchArray([
        'start_quantity' => 7.0,
        'inlet_quantity' => 8.0,
        'outlet_quantity' => 3.0,
        'stocktaking_quantity' => -1.0,
        'waste_quantity' => 2.0,
        'end_quantity' => 9.0,
    ]);

});

it('calculates date range report values', function (): void {
    $user = User::query()->firstOrFail();
    $rawProduct = Product::factory()->rawType()->create();

    $entranceBefore = RawMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-09 20:00:00'),
    ]);
    RawMaterialEntranceItem::create([
        'raw_material_entrance_id' => $entranceBefore->id,
        'product_id' => $rawProduct->id,
        'quantity' => 10,
        'price' => 1,
        'total' => 10,
    ]);

    $outBefore = RawMaterialOut::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-09 21:00:00'),
    ]);
    RawMaterialOutItem::create([
        'raw_material_out_id' => $outBefore->id,
        'product_id' => $rawProduct->id,
        'quantity' => 4,
        'price' => 1,
        'total' => 4,
    ]);

    $stocktakingBefore = Stocktaking::create([
        'user_id' => $user->id,
        'product_type' => ProductType::Raw,
        'total' => 0,
        'closed_at' => Carbon::parse('2026-02-09 22:00:00'),
    ]);
    StocktakingItem::create([
        'stocktaking_id' => $stocktakingBefore->id,
        'product_id' => $rawProduct->id,
        'stock_quantity' => 5,
        'real_quantity' => 7,
        'price' => 1,
        'total' => 2,
    ]);

    $wasteBefore = Waste::create([
        'user_id' => $user->id,
        'type' => ProductType::Raw,
        'total' => 0,
        'closed_at' => Carbon::parse('2026-02-09 23:00:00'),
    ]);
    WastedItem::create([
        'waste_id' => $wasteBefore->id,
        'product_id' => $rawProduct->id,
        'quantity' => 1,
        'price' => 1,
        'total' => 1,
    ]);

    $entranceDayOne = RawMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-10 10:00:00'),
    ]);
    RawMaterialEntranceItem::create([
        'raw_material_entrance_id' => $entranceDayOne->id,
        'product_id' => $rawProduct->id,
        'quantity' => 8,
        'price' => 1,
        'total' => 8,
    ]);

    $outDayOne = RawMaterialOut::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-10 12:00:00'),
    ]);
    RawMaterialOutItem::create([
        'raw_material_out_id' => $outDayOne->id,
        'product_id' => $rawProduct->id,
        'quantity' => 3,
        'price' => 1,
        'total' => 3,
    ]);

    $stocktakingDayOne = Stocktaking::create([
        'user_id' => $user->id,
        'product_type' => ProductType::Raw,
        'total' => 0,
        'closed_at' => Carbon::parse('2026-02-10 14:00:00'),
    ]);
    StocktakingItem::create([
        'stocktaking_id' => $stocktakingDayOne->id,
        'product_id' => $rawProduct->id,
        'stock_quantity' => 10,
        'real_quantity' => 9,
        'price' => 1,
        'total' => -1,
    ]);

    $wasteDayOne = Waste::create([
        'user_id' => $user->id,
        'type' => ProductType::Raw,
        'total' => 0,
        'closed_at' => Carbon::parse('2026-02-10 15:00:00'),
    ]);
    WastedItem::create([
        'waste_id' => $wasteDayOne->id,
        'product_id' => $rawProduct->id,
        'quantity' => 2,
        'price' => 1,
        'total' => 2,
    ]);

    $entranceDayTwo = RawMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-11 10:00:00'),
    ]);
    RawMaterialEntranceItem::create([
        'raw_material_entrance_id' => $entranceDayTwo->id,
        'product_id' => $rawProduct->id,
        'quantity' => 4,
        'price' => 1,
        'total' => 4,
    ]);

    $outDayTwo = RawMaterialOut::factory()->closed()->create([
        'user_id' => $user->id,
        'closed_at' => Carbon::parse('2026-02-11 12:00:00'),
    ]);
    RawMaterialOutItem::create([
        'raw_material_out_id' => $outDayTwo->id,
        'product_id' => $rawProduct->id,
        'quantity' => 1,
        'price' => 1,
        'total' => 1,
    ]);

    $stocktakingDayTwo = Stocktaking::create([
        'user_id' => $user->id,
        'product_type' => ProductType::Raw,
        'total' => 0,
        'closed_at' => Carbon::parse('2026-02-11 14:00:00'),
    ]);
    StocktakingItem::create([
        'stocktaking_id' => $stocktakingDayTwo->id,
        'product_id' => $rawProduct->id,
        'stock_quantity' => 10,
        'real_quantity' => 12,
        'price' => 1,
        'total' => 2,
    ]);

    $wasteDayTwo = Waste::create([
        'user_id' => $user->id,
        'type' => ProductType::Raw,
        'total' => 0,
        'closed_at' => Carbon::parse('2026-02-11 15:00:00'),
    ]);
    WastedItem::create([
        'waste_id' => $wasteDayTwo->id,
        'product_id' => $rawProduct->id,
        'quantity' => 1,
        'price' => 1,
        'total' => 1,
    ]);

    $component = livewire(RawMaterialReport::class)
        ->fillForm([
            'mode' => 'range',
            'start_date' => '2026-02-10',
            'end_date' => '2026-02-11',
        ])
        ->call('generateReport')
        ->loadTable()
        ->assertCanSeeTableRecords([$rawProduct])
        ->assertHasNoFormErrors();

    expect($component->instance()->report)->toMatchArray([
        'start_quantity' => 7.0,
        'inlet_quantity' => 12.0,
        'outlet_quantity' => 4.0,
        'stocktaking_quantity' => 1.0,
        'waste_quantity' => 3.0,
        'end_quantity' => 13.0,
    ]);

});
