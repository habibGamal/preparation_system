<?php

declare(strict_types=1);

use App\Filament\Resources\ManufacturingRecipeResource\Pages\ListManufacturingRecipes;
use App\Filament\Resources\ManufacturingRecipeResource\Pages\ViewManufacturingRecipe;
use App\Models\ManufacturingRecipe;

use function Pest\Livewire\livewire;

it('can render the index page', function () {
    livewire(ListManufacturingRecipes::class)
        ->assertOk();
});

it('can render the view page', function () {
    $recipe = ManufacturingRecipe::factory()->create();

    livewire(ViewManufacturingRecipe::class, [
        'record' => $recipe->id,
    ])
        ->assertOk();
});

it('has columns', function (string $column) {
    livewire(ListManufacturingRecipes::class)
        ->assertTableColumnExists($column);
})->with([
    'id',
    'name',
    'product.name',
    'is_auto_calculated',
    'calculated_from_orders_count',
    'items_count',
]);

it('can filter by product', function () {
    $recipe = ManufacturingRecipe::factory()->create();

    livewire(ListManufacturingRecipes::class)
        ->call('loadTable')
        ->filterTable('product', $recipe->product_id)
        ->assertCanSeeTableRecords([$recipe]);
});

it('can filter by auto-calculated status', function () {
    $autoRecipe = ManufacturingRecipe::factory()->autoCalculated()->create();
    $manualRecipe = ManufacturingRecipe::factory()->create(['is_auto_calculated' => false]);

    livewire(ListManufacturingRecipes::class)
        ->call('loadTable')
        ->filterTable('is_auto_calculated', true)
        ->assertCanSeeTableRecords([$autoRecipe])
        ->assertCanNotSeeTableRecords([$manualRecipe]);
});

it('shows calculation metadata for auto-calculated recipes', function () {
    $recipe = ManufacturingRecipe::factory()->autoCalculated()->create([
        'calculated_from_orders_count' => 5,
    ]);

    livewire(ListManufacturingRecipes::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$recipe]);
});

it('shows recalculate action only for auto-calculated recipes', function () {
    $autoRecipe = ManufacturingRecipe::factory()->autoCalculated()->create();
    $manualRecipe = ManufacturingRecipe::factory()->create(['is_auto_calculated' => false]);

    livewire(ListManufacturingRecipes::class)
        ->call('loadTable')
        ->assertTableActionVisible('recalculate', $autoRecipe)
        ->assertTableActionHidden('recalculate', $manualRecipe);
});
