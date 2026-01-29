<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ManufacturingRecipeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturing_recipe_id',
        'product_id',
        'quantity',
        'usage_frequency',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'usage_frequency' => 'decimal:2',
    ];

    public function manufacturingRecipe(): BelongsTo
    {
        return $this->belongsTo(ManufacturingRecipe::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
