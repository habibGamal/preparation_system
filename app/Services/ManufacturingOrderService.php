<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ManufacturingOrderStatus;
use App\Enums\SettingKey;
use App\Models\Inventory;
use App\Models\ManufacturingOrder;
use Illuminate\Support\Facades\DB;

final class ManufacturingOrderService
{
    public function __construct(
        protected RecipeCalculationService $recipeCalculationService
    ) {
    }

    /**
     * Complete a manufacturing order:
     * - Decrement raw materials from inventory
     * - Increment manufactured product in inventory
     * - Set status to completed
     * - Auto-update recipe from historical data (only if max orders not reached)
     */
    public function complete(ManufacturingOrder $order): void
    {
        DB::transaction(function () use ($order): void {
            // Decrement raw materials from inventory
            foreach ($order->items as $item) {
                $inventory = Inventory::where('product_id', $item->product_id)->first();
                if ($inventory) {
                    $inventory->decrement('quantity', $item->quantity);
                }
            }

            // Increment manufactured product in inventory
            $manufacturedInventory = Inventory::where('product_id', $order->product_id)->first();
            if ($manufacturedInventory) {
                $manufacturedInventory->increment('quantity', $order->output_quantity);
            }

            $order->status = ManufacturingOrderStatus::Completed;
            $order->completed_at = now();
            $order->save();

            // Auto-update recipe only if we haven't reached the maximum order limit
            if (setting_bool(SettingKey::AutoUpdateRecipeOnCompletion, true)) {
                if (! $this->recipeCalculationService->hasReachedMaximumOrders($order->product)) {
                    $this->recipeCalculationService->calculateRecipeFromOrders($order->product);
                }
            }
        });
    }

    /**
     * Get variance warnings by comparing order against calculated recipe.
     */
    public function getVarianceWarnings(ManufacturingOrder $order): array
    {
        return $this->recipeCalculationService->getVarianceFromRecipe($order);
    }

    /**
     * Clone a manufacturing order.
     */
    public function clone(ManufacturingOrder $order): ManufacturingOrder
    {
        return DB::transaction(function () use ($order): ManufacturingOrder {
            $clonedOrder = ManufacturingOrder::create([
                'user_id' => auth()->id(),
                'product_id' => $order->product_id,
                'status' => ManufacturingOrderStatus::Draft,
                'output_quantity' => $order->output_quantity,
                'notes' => $order->notes,
                'completed_at' => null,
            ]);

            foreach ($order->items as $item) {
                $clonedOrder->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ]);
            }

            return $clonedOrder;
        });
    }
}
