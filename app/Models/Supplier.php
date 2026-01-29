<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
    ];

    public function rawMaterialEntrances(): HasMany
    {
        return $this->hasMany(RawMaterialEntrance::class);
    }

    public function manufacturedMaterialEntrances(): HasMany
    {
        return $this->hasMany(ManufacturedMaterialEntrance::class);
    }
}
