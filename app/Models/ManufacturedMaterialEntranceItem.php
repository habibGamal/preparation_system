<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ManufacturedMaterialEntranceItem extends Model
{
    protected $fillable = [
        'manufactured_material_entrance_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function manufacturedMaterialEntrance(): BelongsTo
    {
        return $this->belongsTo(ManufacturedMaterialEntrance::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
