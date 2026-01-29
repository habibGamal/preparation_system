<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MaterialEntranceStatus;
use App\Models\Inventory;
use App\Models\ManufacturedMaterialOut;
use Illuminate\Support\Facades\DB;

final class ManufacturedMaterialOutService
{
    public function close(ManufacturedMaterialOut $out): void
    {
        DB::transaction(function () use ($out): void {
            foreach ($out->items as $item) {
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $item->product_id],
                    ['quantity' => 0]
                );

                $inventory->decrement('quantity', $item->quantity);
            }

            $out->status = MaterialEntranceStatus::Closed;
            $out->closed_at = now();
            $out->save();
        });
    }

    public function clone(ManufacturedMaterialOut $out): ManufacturedMaterialOut
    {
        return DB::transaction(function () use ($out): ManufacturedMaterialOut {
            $clonedOut = ManufacturedMaterialOut::create([
                'user_id' => auth()->id(),
                'consumer_id' => $out->consumer_id,
                'status' => MaterialEntranceStatus::Draft,
                'total' => $out->total,
                'closed_at' => null,
            ]);

            foreach ($out->items as $item) {
                $clonedOut->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ]);
            }

            return $clonedOut;
        });
    }
}
