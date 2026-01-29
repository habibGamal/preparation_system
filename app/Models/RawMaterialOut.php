<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MaterialEntranceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RawMaterialOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consumer_id',
        'status',
        'total',
        'closed_at',
    ];

    protected $casts = [
        'status' => MaterialEntranceStatus::class,
        'total' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(Consumer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RawMaterialOutItem::class);
    }
}
