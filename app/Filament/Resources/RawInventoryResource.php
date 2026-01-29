<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RawInventoryResource\Pages;
use App\Filament\Resources\RawInventoryResource\RelationManagers;
use App\Filament\Resources\RawInventoryResource\Tables\RawInventoriesTable;
use App\Models\Inventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class RawInventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة المخزون الخام';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'مخزون المواد الخام';

    protected static ?string $modelLabel = 'مخزون مواد خام';

    protected static ?string $pluralModelLabel = 'مخزون المواد الخام';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('product', fn (Builder $query) => $query->where('type', 'raw'));
    }

    public static function table(Table $table): Table
    {
        return RawInventoriesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            'rawMaterialEntranceItems' => RelationManagers\RawMaterialEntranceItemsRelationManager::class,
            'rawMaterialOutItems' => RelationManagers\RawMaterialOutItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRawInventories::route('/'),
            'view' => Pages\ViewRawInventory::route('/{record}'),
        ];
    }
}
