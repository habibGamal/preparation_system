<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MaterialEntranceStatus;
use App\Models\Inventory;
use App\Models\RawMaterialEntrance;
use Illuminate\Support\Facades\DB;

final class RawMaterialEntranceService
{
    public function close(RawMaterialEntrance $entrance): void
    {
        DB::transaction(function () use ($entrance): void {
            foreach ($entrance->items as $item) {
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $item->product_id],
                    ['quantity' => 0]
                );

                $inventory->increment('quantity', $item->quantity);
            }

            $entrance->status = MaterialEntranceStatus::Closed;
            $entrance->closed_at = now();
            $entrance->save();
        });
    }

    public function clone(RawMaterialEntrance $entrance): RawMaterialEntrance
    {
        return DB::transaction(function () use ($entrance): RawMaterialEntrance {
            $clonedEntrance = RawMaterialEntrance::create([
                'user_id' => auth()->id(),
                'supplier_id' => $entrance->supplier_id,
                'status' => MaterialEntranceStatus::Draft,
                'total' => $entrance->total,
                'closed_at' => null,
            ]);

            foreach ($entrance->items as $item) {
                $clonedEntrance->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ]);
            }

            return $clonedEntrance;
        });
    }
}
