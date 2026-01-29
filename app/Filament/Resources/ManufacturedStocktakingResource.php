<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ProductType;
use App\Filament\Resources\ManufacturedStocktakingResource\Pages;
use App\Filament\Resources\ManufacturedStocktakingResource\Schemas\ManufacturedStocktakingForm;
use App\Filament\Resources\ManufacturedStocktakingResource\Tables\ManufacturedStocktakingsTable;
use App\Models\Stocktaking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ManufacturedStocktakingResource extends Resource
{
    protected static ?string $model = Stocktaking::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة المخزون المصنع';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'جرد المنتجات المصنعة';

    protected static ?string $modelLabel = 'جرد منتجات مصنعة';

    protected static ?string $pluralModelLabel = 'جرد المنتجات المصنعة';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('product_type', ProductType::Manufactured->value);
    }

    public static function form(Schema $schema): Schema
    {
        return ManufacturedStocktakingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManufacturedStocktakingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManufacturedStocktakings::route('/'),
            'create' => Pages\CreateManufacturedStocktaking::route('/create'),
            'view' => Pages\ViewManufacturedStocktaking::route('/{record}'),
            'edit' => Pages\EditManufacturedStocktaking::route('/{record}/edit'),
        ];
    }
}
