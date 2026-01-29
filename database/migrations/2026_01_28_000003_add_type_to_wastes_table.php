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
        Schema::table('wastes', function (Blueprint $table): void {
            $table->string('type')->default(ProductType::Raw->value)->after('user_id');
        });
    }
};
