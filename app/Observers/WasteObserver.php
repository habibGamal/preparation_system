<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Waste;
use Illuminate\Support\Facades\DB;

final class WasteObserver
{
    public function updating(Waste $waste): void
    {
        // Prevent updates when closed
        if ($waste->isClosed() && $waste->isDirty() && ! $waste->isDirty('closed_at')) {
            abort(403, 'لا يمكن تعديل التالف بعد الإغلاق');
        }

        // When closing the waste record
        if ($waste->isDirty('closed_at') && $waste->closed_at !== null) {
            DB::transaction(function () use ($waste): void {
                // Calculate total from items
                $total = 0;

                foreach ($waste->wastedItems as $item) {
                    $itemTotal = (float) $item->quantity * (float) $item->price;
                    $total += $itemTotal;

                    // Remove wasted quantity from inventory
                    $inventory = $item->product->inventory;
                    if ($inventory !== null) {
                        $inventory->quantity -= (float) $item->quantity;
                        $inventory->save();
                    }
                }

                $waste->total = $total;
            });
        }
    }

    public function deleting(Waste $waste): void
    {
        // Prevent deletion when closed
        if ($waste->isClosed()) {
            abort(403, 'لا يمكن حذف التالف بعد الإغلاق');
        }
    }
}
