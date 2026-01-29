<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductType;
use App\Enums\ProductUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'barcode',
        'price',
        'cost',
        'min_stock',
        'avg_purchase_quantity',
        'type',
        'unit',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'type' => ProductType::class,
        'unit' => ProductUnit::class,
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function rawMaterialEntranceItems(): HasMany
    {
        return $this->hasMany(RawMaterialEntranceItem::class);
    }

    public function rawMaterialOutItems(): HasMany
    {
        return $this->hasMany(RawMaterialOutItem::class);
    }

    public function manufacturedMaterialEntranceItems(): HasMany
    {
        return $this->hasMany(ManufacturedMaterialEntranceItem::class);
    }

    public function manufacturedMaterialOutItems(): HasMany
    {
        return $this->hasMany(ManufacturedMaterialOutItem::class);
    }

    public function rawMaterialEntrances(): HasManyThrough
    {
        return $this->hasManyThrough(
            RawMaterialEntrance::class,
            RawMaterialEntranceItem::class,
            'product_id',
            'id',
            'id',
            'raw_material_entrance_id'
        );
    }

    public function rawMaterialOuts(): HasManyThrough
    {
        return $this->hasManyThrough(
            RawMaterialOut::class,
            RawMaterialOutItem::class,
            'product_id',
            'id',
            'id',
            'raw_material_out_id'
        );
    }

    public function manufacturedMaterialEntrances(): HasManyThrough
    {
        return $this->hasManyThrough(
            ManufacturedMaterialEntrance::class,
            ManufacturedMaterialEntranceItem::class,
            'product_id',
            'id',
            'id',
            'manufactured_material_entrance_id'
        );
    }

    public function manufacturedMaterialOuts(): HasManyThrough
    {
        return $this->hasManyThrough(
            ManufacturedMaterialOut::class,
            ManufacturedMaterialOutItem::class,
            'product_id',
            'id',
            'id',
            'manufactured_material_out_id'
        );
    }

    /**
     * Recipes where this product is the manufactured output
     */
    public function manufacturingRecipes(): HasMany
    {
        return $this->hasMany(ManufacturingRecipe::class);
    }

    /**
     * Recipe items where this product is used as raw material
     */
    public function manufacturingRecipeItems(): HasMany
    {
        return $this->hasMany(ManufacturingRecipeItem::class);
    }

    /**
     * Manufacturing orders where this product is the manufactured output
     */
    public function manufacturingOrders(): HasMany
    {
        return $this->hasMany(ManufacturingOrder::class);
    }

    /**
     * Manufacturing order items where this product is used as raw material
     */
    public function manufacturingOrderItems(): HasMany
    {
        return $this->hasMany(ManufacturingOrderItem::class);
    }

    /**
     * Stocktaking items for this product
     */
    public function stocktakingItems(): HasMany
    {
        return $this->hasMany(StocktakingItem::class);
    }

    /**
     * Wasted items for this product
     */
    public function wastedItems(): HasMany
    {
        return $this->hasMany(WastedItem::class);
    }
}
