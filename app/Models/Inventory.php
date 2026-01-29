<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

final class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function rawMaterialEntranceItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            RawMaterialEntranceItem::class,
            Product::class,
            'id',
            'product_id',
            'product_id',
            'id'
        );
    }

    public function rawMaterialOutItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            RawMaterialOutItem::class,
            Product::class,
            'id',
            'product_id',
            'product_id',
            'id'
        );
    }

    public function manufacturedMaterialEntranceItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            ManufacturedMaterialEntranceItem::class,
            Product::class,
            'id',
            'product_id',
            'product_id',
            'id'
        );
    }

    public function manufacturedMaterialOutItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            ManufacturedMaterialOutItem::class,
            Product::class,
            'id',
            'product_id',
            'product_id',
            'id'
        );
    }
}
