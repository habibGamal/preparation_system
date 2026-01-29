<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocktaking_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stocktaking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('stock_quantity', 10, 2);
            $table->decimal('real_quantity', 10, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }
};
