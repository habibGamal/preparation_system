<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ManufacturedProductResource\Pages;
use App\Filament\Resources\ManufacturedProductResource\Schemas\ManufacturedProductForm;
use App\Filament\Resources\ManufacturedProductResource\Tables\ManufacturedProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ManufacturedProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube-transparent';

    protected static string|UnitEnum|null $navigationGroup = 'الكتالوج';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'المصنعات';

    protected static ?string $modelLabel = 'منتج مصنع';

    protected static ?string $pluralModelLabel = 'المصنعات';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'manufactured');
    }

    public static function form(Schema $schema): Schema
    {
        return ManufacturedProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManufacturedProductsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManufacturedProducts::route('/'),
            'create' => Pages\CreateManufacturedProduct::route('/create'),
            'view' => Pages\ViewManufacturedProduct::route('/{record}'),
            'edit' => Pages\EditManufacturedProduct::route('/{record}/edit'),
        ];
    }
}
