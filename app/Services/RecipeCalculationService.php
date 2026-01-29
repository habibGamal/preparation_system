<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ManufacturingOrderStatus;
use App\Enums\SettingKey;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingRecipe;
use App\Models\ManufacturingRecipeItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class RecipeCalculationService
{
    /**
     * Calculate or update recipe from completed orders.
     *
     * The recipe stores consumption rates (raw material per unit of manufactured product)
     * and usage frequency (% of orders that used each ingredient).
     */
    public function calculateRecipeFromOrders(Product $product, ?int $maximumOrders = null): ?ManufacturingRecipe
    {
        $minimumOrders = setting_int(SettingKey::MinimumOrdersForRecipe, 3);
        $maximumOrders = $maximumOrders ?? setting_int(SettingKey::MaximumOrdersForRecipe, 10);
        $includeThreshold = setting_float(SettingKey::IncludeIngredientThreshold, 30);

        // Get most recent completed orders (up to maximum)
        $orders = ManufacturingOrder::query()
            ->where('product_id', $product->id)
            ->where('status', ManufacturingOrderStatus::Completed)
            ->orderBy('completed_at', 'desc')
            ->limit($maximumOrders)
            ->with('items.product')
            ->get();

        if ($orders->count() < $minimumOrders) {
            return null;
        }

        return DB::transaction(function () use ($product, $orders, $includeThreshold): ManufacturingRecipe {
            $totalOrders = $orders->count();

            // Calculate average output quantity
            $avgOutputQuantity = $orders->avg(fn ($order) => (float) $order->output_quantity);

            // Calculate consumption rates and frequencies for each ingredient
            $ingredientStats = $this->calculateIngredientStats($orders);

            // Create or update recipe
            $recipe = ManufacturingRecipe::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'name' => 'متوسط تصنيع '.$product->name,
                    'expected_output_quantity' => round($avgOutputQuantity, 2),
                    'is_auto_calculated' => true,
                    'calculated_from_orders_count' => $totalOrders,
                    'last_calculated_at' => now(),
                    'notes' => "محسوب تلقائياً من {$totalOrders} أذون تصنيع (آخر الأوامر المكتملة)",
                ]
            );

            // Update recipe items - only include ingredients above threshold
            $recipe->items()->delete();

            foreach ($ingredientStats as $productId => $stats) {
                // Skip ingredients with low usage frequency
                if ($stats['usage_frequency'] < $includeThreshold) {
                    continue;
                }

                ManufacturingRecipeItem::create([
                    'manufacturing_recipe_id' => $recipe->id,
                    'product_id' => $productId,
                    'quantity' => round($stats['avg_consumption_rate'], 3),
                    'usage_frequency' => round($stats['usage_frequency'], 2),
                ]);
            }

            return $recipe->fresh('items');
        });
    }

    /**
     * Calculate consumption rate statistics for each ingredient.
     *
     * For each ingredient, we calculate:
     * - Average consumption rate (quantity used / output quantity)
     * - Usage frequency (% of orders that used this ingredient)
     */
    protected function calculateIngredientStats(Collection $orders): array
    {
        $totalOrders = $orders->count();
        $ingredientRates = []; // [product_id => [rate1, rate2, ...]]
        $ingredientCounts = []; // [product_id => count of orders that used it]

        foreach ($orders as $order) {
            $outputQuantity = (float) $order->output_quantity;

            if ($outputQuantity <= 0) {
                continue;
            }

            foreach ($order->items as $item) {
                $productId = $item->product_id;
                $quantity = (float) $item->quantity;

                // Calculate consumption rate for this order
                $consumptionRate = $quantity / $outputQuantity;

                if (! isset($ingredientRates[$productId])) {
                    $ingredientRates[$productId] = [];
                    $ingredientCounts[$productId] = 0;
                }

                $ingredientRates[$productId][] = $consumptionRate;
                $ingredientCounts[$productId]++;
            }
        }

        // Calculate averages and frequencies
        $stats = [];
        foreach ($ingredientRates as $productId => $rates) {
            $stats[$productId] = [
                'avg_consumption_rate' => array_sum($rates) / count($rates),
                'usage_frequency' => ($ingredientCounts[$productId] / $totalOrders) * 100,
                'orders_count' => $ingredientCounts[$productId],
            ];
        }

        return $stats;
    }

    /**
     * Get variance warnings by comparing order against calculated recipe.
     *
     * Calculates expected quantities based on recipe consumption rates
     * and compares with actual quantities used.
     */
    public function getVarianceFromRecipe(ManufacturingOrder $order): array
    {
        $recipe = ManufacturingRecipe::where('product_id', $order->product_id)
            ->where('is_auto_calculated', true)
            ->first();

        if (! $recipe) {
            return [];
        }

        $warnings = [];
        $threshold = setting_float(SettingKey::VarianceWarningThreshold, 10.0);
        $requiredThreshold = setting_float(SettingKey::RequiredIngredientThreshold, 70);
        $outputQuantity = (float) $order->output_quantity;

        if ($outputQuantity <= 0) {
            return [];
        }

        // Check each recipe ingredient
        foreach ($recipe->items as $recipeItem) {
            $expectedQuantity = (float) $recipeItem->quantity * $outputQuantity;
            $orderItem = $order->items->firstWhere('product_id', $recipeItem->product_id);

            if ($orderItem) {
                // Ingredient was used - check variance
                $actualQuantity = (float) $orderItem->quantity;
                $variance = $this->calculateVariancePercentage($actualQuantity, $expectedQuantity);

                if (abs($variance) > $threshold) {
                    $warnings[] = [
                        'type' => 'raw_material',
                        'product' => $recipeItem->product->name,
                        'expected' => round($expectedQuantity, 2),
                        'actual' => $actualQuantity,
                        'variance' => round($variance, 1),
                        'message' => "{$recipeItem->product->name}: متوقع ".round($expectedQuantity, 2)."، فعلي {$actualQuantity} (انحراف ".abs(round($variance, 1)).'%)',
                    ];
                }
            } elseif ($recipeItem->usage_frequency >= $requiredThreshold) {
                // Required ingredient is missing
                $warnings[] = [
                    'type' => 'missing_ingredient',
                    'product' => $recipeItem->product->name,
                    'expected' => round($expectedQuantity, 2),
                    'actual' => 0,
                    'variance' => -100,
                    'message' => "{$recipeItem->product->name}: خام أساسي غير موجود (متوقع ".round($expectedQuantity, 2).')',
                ];
            }
        }

        // Check for extra ingredients not in recipe
        foreach ($order->items as $orderItem) {
            $recipeItem = $recipe->items->firstWhere('product_id', $orderItem->product_id);

            if (! $recipeItem) {
                $warnings[] = [
                    'type' => 'extra_ingredient',
                    'product' => $orderItem->product->name,
                    'expected' => 0,
                    'actual' => (float) $orderItem->quantity,
                    'variance' => 100,
                    'message' => "{$orderItem->product->name}: خام إضافي غير متوقع (كمية {$orderItem->quantity})",
                ];
            }
        }

        return $warnings;
    }

    /**
     * Calculate variance percentage.
     */
    protected function calculateVariancePercentage(float $actual, float $expected): float
    {
        if ($expected == 0) {
            return $actual > 0 ? 100 : 0;
        }

        return (($actual - $expected) / $expected) * 100;
    }

    /**
     * Check if product has enough orders to calculate recipe.
     */
    public function hasEnoughOrdersForRecipe(Product $product): bool
    {
        $minimumOrders = setting_int(SettingKey::MinimumOrdersForRecipe, 3);

        $ordersCount = ManufacturingOrder::query()
            ->where('product_id', $product->id)
            ->where('status', ManufacturingOrderStatus::Completed)
            ->count();

        return $ordersCount >= $minimumOrders;
    }

    /**
     * Check if product has reached the maximum order limit for recipe calculation.
     * After reaching this limit, recipes should no longer auto-update.
     */
    public function hasReachedMaximumOrders(Product $product): bool
    {
        $maximumOrders = setting_int(SettingKey::MaximumOrdersForRecipe, 10);

        $ordersCount = ManufacturingOrder::query()
            ->where('product_id', $product->id)
            ->where('status', ManufacturingOrderStatus::Completed)
            ->count();

        return $ordersCount >= $maximumOrders;
    }

    /**
     * Get recipe for product or null if not exists.
     */
    public function getRecipeForProduct(Product $product): ?ManufacturingRecipe
    {
        return ManufacturingRecipe::where('product_id', $product->id)
            ->where('is_auto_calculated', true)
            ->first();
    }

    /**
     * Determine ingredient type based on usage frequency.
     */
    public function getIngredientType(float $usageFrequency): string
    {
        $requiredThreshold = setting_float(SettingKey::RequiredIngredientThreshold, 70);
        $includeThreshold = setting_float(SettingKey::IncludeIngredientThreshold, 30);

        if ($usageFrequency >= $requiredThreshold) {
            return 'required'; // أساسي
        }

        if ($usageFrequency >= $includeThreshold) {
            return 'optional'; // اختياري
        }

        return 'rare'; // نادر
    }
}
