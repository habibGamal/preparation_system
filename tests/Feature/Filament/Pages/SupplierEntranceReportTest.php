<?php

declare(strict_types=1);

use App\Filament\Pages\SupplierEntranceReport;
use App\Models\ManufacturedMaterialEntrance;
use App\Models\ManufacturedMaterialEntranceItem;
use App\Models\Product;
use App\Models\RawMaterialEntrance;
use App\Models\RawMaterialEntranceItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->create());
});

it('can render the supplier entrance report page', function (): void {
    livewire(SupplierEntranceReport::class)
        ->assertOk();
});

it('shows only selected supplier raw entrance items in chosen day', function (): void {
    $user = User::query()->firstOrFail();
    $supplier = Supplier::factory()->create();
    $otherSupplier = Supplier::factory()->create();
    $rawProduct = Product::factory()->rawType()->create();

    $entranceForSupplier = RawMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'closed_at' => Carbon::parse('2026-03-10 09:00:00'),
    ]);
    $itemForSupplierFirst = RawMaterialEntranceItem::create([
        'raw_material_entrance_id' => $entranceForSupplier->id,
        'product_id' => $rawProduct->id,
        'quantity' => 5,
        'price' => 2,
        'total' => 10,
    ]);
    $itemForSupplierSecond = RawMaterialEntranceItem::create([
        'raw_material_entrance_id' => $entranceForSupplier->id,
        'product_id' => $rawProduct->id,
        'quantity' => 7,
        'price' => 4,
        'total' => 28,
    ]);

    $entranceForOtherSupplier = RawMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'supplier_id' => $otherSupplier->id,
        'closed_at' => Carbon::parse('2026-03-10 10:00:00'),
    ]);
    $itemForOtherSupplier = RawMaterialEntranceItem::create([
        'raw_material_entrance_id' => $entranceForOtherSupplier->id,
        'product_id' => $rawProduct->id,
        'quantity' => 6,
        'price' => 2,
        'total' => 12,
    ]);

    livewire(SupplierEntranceReport::class)
        ->fillForm([
            'supplier_id' => $supplier->id,
            'mode' => 'single',
            'single_date' => '2026-03-10',
        ])
        ->call('generateReport')
        ->loadTable()
        ->assertCanSeeTableRecords([$itemForSupplierFirst])
        ->assertCanNotSeeTableRecords([$itemForSupplierSecond])
        ->assertCanNotSeeTableRecords([$itemForOtherSupplier]);
});

it('shows only selected supplier manufactured entrance items in chosen range', function (): void {
    $user = User::query()->firstOrFail();
    $supplier = Supplier::factory()->create();
    $otherSupplier = Supplier::factory()->create();
    $manufacturedProduct = Product::factory()->manufacturedType()->create();

    $inRangeEntrance = ManufacturedMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'closed_at' => Carbon::parse('2026-03-11 09:00:00'),
    ]);
    $inRangeItemFirst = ManufacturedMaterialEntranceItem::create([
        'manufactured_material_entrance_id' => $inRangeEntrance->id,
        'product_id' => $manufacturedProduct->id,
        'quantity' => 7,
        'price' => 3,
        'total' => 21,
    ]);
    $inRangeItemSecond = ManufacturedMaterialEntranceItem::create([
        'manufactured_material_entrance_id' => $inRangeEntrance->id,
        'product_id' => $manufacturedProduct->id,
        'quantity' => 5,
        'price' => 5,
        'total' => 25,
    ]);

    $outOfRangeEntrance = ManufacturedMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'supplier_id' => $supplier->id,
        'closed_at' => Carbon::parse('2026-03-15 09:00:00'),
    ]);
    $outOfRangeItem = ManufacturedMaterialEntranceItem::create([
        'manufactured_material_entrance_id' => $outOfRangeEntrance->id,
        'product_id' => $manufacturedProduct->id,
        'quantity' => 4,
        'price' => 3,
        'total' => 12,
    ]);

    $otherSupplierEntrance = ManufacturedMaterialEntrance::factory()->closed()->create([
        'user_id' => $user->id,
        'supplier_id' => $otherSupplier->id,
        'closed_at' => Carbon::parse('2026-03-11 12:00:00'),
    ]);
    $otherSupplierItem = ManufacturedMaterialEntranceItem::create([
        'manufactured_material_entrance_id' => $otherSupplierEntrance->id,
        'product_id' => $manufacturedProduct->id,
        'quantity' => 8,
        'price' => 3,
        'total' => 24,
    ]);

    livewire(SupplierEntranceReport::class)
        ->set('activeTab', 'manufactured')
        ->fillForm([
            'supplier_id' => $supplier->id,
            'mode' => 'range',
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-12',
        ])
        ->call('generateReport')
        ->loadTable()
        ->assertCanSeeTableRecords([$inRangeItemFirst])
        ->assertCanNotSeeTableRecords([$inRangeItemSecond])
        ->assertCanNotSeeTableRecords([$outOfRangeItem])
        ->assertCanNotSeeTableRecords([$otherSupplierItem]);
});
