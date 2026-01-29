<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturing_recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete(); // Raw material
            $table->decimal('quantity', 10, 3); // Consumption rate per unit of manufactured product
            $table->decimal('usage_frequency', 5, 2)->default(100); // % of orders that use this ingredient
            $table->timestamps();
        });
    }
};
