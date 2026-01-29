<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ManufacturingOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ManufacturingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'status',
        'output_quantity',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'status' => ManufacturingOrderStatus::class,
        'output_quantity' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManufacturingOrderItem::class);
    }
}
