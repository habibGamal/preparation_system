<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Consumer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
    ];

    public function rawMaterialOuts(): HasMany
    {
        return $this->hasMany(RawMaterialOut::class);
    }

    public function manufacturedMaterialOuts(): HasMany
    {
        return $this->hasMany(ManufacturedMaterialOut::class);
    }
}
