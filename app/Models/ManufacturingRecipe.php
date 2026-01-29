<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ManufacturingRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_id',
        'expected_output_quantity',
        'is_auto_calculated',
        'calculated_from_orders_count',
        'last_calculated_at',
        'notes',
    ];

    protected $casts = [
        'expected_output_quantity' => 'decimal:2',
        'is_auto_calculated' => 'boolean',
        'calculated_from_orders_count' => 'integer',
        'last_calculated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManufacturingRecipeItem::class);
    }

    public function manufacturingOrders(): HasMany
    {
        return $this->hasMany(ManufacturingOrder::class);
    }
}
