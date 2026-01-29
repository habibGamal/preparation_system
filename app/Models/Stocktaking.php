<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Stocktaking extends Model
{
    protected $fillable = [
        'user_id',
        'product_type',
        'total',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'product_type' => ProductType::class,
        'total' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StocktakingItem::class);
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    public function canBeEdited(): bool
    {
        return ! $this->isClosed();
    }

    public function canBeDeleted(): bool
    {
        return ! $this->isClosed();
    }
}
