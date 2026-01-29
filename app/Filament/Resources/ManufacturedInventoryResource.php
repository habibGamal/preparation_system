<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ManufacturedInventoryResource\Pages;
use App\Filament\Resources\ManufacturedInventoryResource\RelationManagers;
use App\Filament\Resources\ManufacturedInventoryResource\Tables\ManufacturedInventoriesTable;
use App\Models\Inventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ManufacturedInventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة المخزون المصنع';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'مخزون المصنعات';

    protected static ?string $modelLabel = 'مخزون مصنعات';

    protected static ?string $pluralModelLabel = 'مخزون المصنعات';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('product', fn (Builder $query) => $query->where('type', 'manufactured'));
    }

    public static function table(Table $table): Table
    {
        return ManufacturedInventoriesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            'manufacturedMaterialEntranceItems' => RelationManagers\ManufacturedMaterialEntranceItemsRelationManager::class,
            'manufacturedMaterialOutItems' => RelationManagers\ManufacturedMaterialOutItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManufacturedInventories::route('/'),
            'view' => Pages\ViewManufacturedInventory::route('/{record}'),
        ];
    }
}
