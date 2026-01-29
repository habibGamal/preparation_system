<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Waste extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'total',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'type' => ProductType::class,
        'total' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wastedItems(): HasMany
    {
        return $this->hasMany(WastedItem::class);
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
