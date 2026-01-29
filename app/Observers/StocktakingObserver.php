<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Stocktaking;
use Illuminate\Support\Facades\DB;

final class StocktakingObserver
{
    public function updating(Stocktaking $stocktaking): void
    {
        // Prevent updates when closed
        if ($stocktaking->isClosed() && $stocktaking->isDirty() && ! $stocktaking->isDirty('closed_at')) {
            abort(403, 'لا يمكن تعديل الجرد بعد الإغلاق');
        }

        // When closing the stocktaking
        if ($stocktaking->isDirty('closed_at') && $stocktaking->closed_at !== null) {
            DB::transaction(function () use ($stocktaking): void {
                // Calculate total from items
                $total = 0;

                foreach ($stocktaking->items as $item) {
                    $variance = $item->getVariance();
                    $itemTotal = $variance * (float) $item->price;
                    $total += $itemTotal;

                    // Update inventory based on variance
                    $inventory = $item->product->inventory;
                    if ($inventory !== null) {
                        $inventory->quantity += $variance;
                        $inventory->save();
                    }
                }

                $stocktaking->total = $total;
            });
        }
    }

    public function deleting(Stocktaking $stocktaking): void
    {
        // Prevent deletion when closed
        if ($stocktaking->isClosed()) {
            abort(403, 'لا يمكن حذف الجرد بعد الإغلاق');
        }
    }
}
