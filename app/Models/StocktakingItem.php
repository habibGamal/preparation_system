<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StocktakingItem extends Model
{
    protected $fillable = [
        'stocktaking_id',
        'product_id',
        'stock_quantity',
        'real_quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'stock_quantity' => 'decimal:2',
        'real_quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function stocktaking(): BelongsTo
    {
        return $this->belongsTo(Stocktaking::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getVariance(): float
    {
        return (float) ($this->real_quantity - $this->stock_quantity);
    }

    public function calculateTotal(): float
    {
        return $this->getVariance() * (float) $this->price;
    }
}
