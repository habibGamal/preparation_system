<?php

declare(strict_types=1);

use App\Enums\MaterialEntranceStatus;
use App\Models\RawMaterialEntrance;
use App\Models\Supplier;
use App\Models\User;

it('allows update on draft raw material entrance', function () {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();
    $entrance = RawMaterialEntrance::factory()->create([
        'status' => MaterialEntranceStatus::Draft,
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
    ]);

    expect($user->can('update', $entrance))->toBeTrue();
});

it('prevents update on closed raw material entrance', function () {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();
    $entrance = RawMaterialEntrance::factory()->create([
        'status' => MaterialEntranceStatus::Closed,
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
    ]);

    expect($user->can('update', $entrance))->toBeFalse();
});

it('allows delete on draft raw material entrance', function () {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();
    $entrance = RawMaterialEntrance::factory()->create([
        'status' => MaterialEntranceStatus::Draft,
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
    ]);

    expect($user->can('delete', $entrance))->toBeTrue();
});

it('prevents delete on closed raw material entrance', function () {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();
    $entrance = RawMaterialEntrance::factory()->create([
        'status' => MaterialEntranceStatus::Closed,
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
    ]);

    expect($user->can('delete', $entrance))->toBeFalse();
});

it('always allows view any', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', RawMaterialEntrance::class))->toBeTrue();
});

it('always allows view', function () {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();
    $entrance = RawMaterialEntrance::factory()->create([
        'status' => MaterialEntranceStatus::Closed,
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
    ]);

    expect($user->can('view', $entrance))->toBeTrue();
});

it('always allows create', function () {
    $user = User::factory()->create();

    expect($user->can('create', RawMaterialEntrance::class))->toBeTrue();
});
