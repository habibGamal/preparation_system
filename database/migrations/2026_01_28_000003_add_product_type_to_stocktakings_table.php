<?php

declare(strict_types=1);

use App\Enums\ProductType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocktakings', function (Blueprint $table): void {
            $table->string('product_type')->after('user_id')->default(ProductType::Raw->value);
        });
    }
};
