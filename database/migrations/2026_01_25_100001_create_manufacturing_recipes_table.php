<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete(); // Manufactured product
            $table->decimal('expected_output_quantity', 10, 2)->default(1);
            $table->boolean('is_auto_calculated')->default(true);
            $table->integer('calculated_from_orders_count')->default(0);
            $table->integer('calculation_period_days')->default(60);
            $table->timestamp('last_calculated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
