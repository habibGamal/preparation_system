<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Recipe Calculation Settings
    |--------------------------------------------------------------------------
    |
    | Configure how manufacturing recipes are automatically calculated
    | from historical manufacturing orders.
    |
    */

    // Minimum number of completed orders required to generate a reliable recipe
    'minimum_orders_for_recipe' => env('MANUFACTURING_MIN_ORDERS_FOR_RECIPE', 3),

    // Maximum number of orders to use for recipe calculation
    // After this limit is reached, recipe will no longer auto-update on order completion
    'maximum_orders_for_recipe' => env('MANUFACTURING_MAX_ORDERS_FOR_RECIPE', 10),

    // Variance warning threshold (percentage) - deviation from recipe
    'variance_warning_threshold' => env('MANUFACTURING_VARIANCE_THRESHOLD', 10.0),

    // Automatically update recipe after each order completion (until max orders reached)
    'auto_update_recipe_on_completion' => env('MANUFACTURING_AUTO_UPDATE_RECIPE', true),

    /*
    |--------------------------------------------------------------------------
    | Ingredient Frequency Thresholds
    |--------------------------------------------------------------------------
    |
    | Configure how ingredients are classified based on their usage frequency.
    | - Required: appears in most orders, warning if missing
    | - Optional: appears sometimes, no warning if missing
    | - Rare: appears rarely, ignored in calculations
    |
    */

    // Minimum frequency (%) to consider an ingredient as required (warning if missing)
    'required_ingredient_threshold' => env('MANUFACTURING_REQUIRED_THRESHOLD', 70),

    // Minimum frequency (%) to include an ingredient in recipe (below = ignored)
    'include_ingredient_threshold' => env('MANUFACTURING_INCLUDE_THRESHOLD', 30),
];
