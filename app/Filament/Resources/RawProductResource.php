<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RawProductResource\Pages;
use App\Filament\Resources\RawProductResource\Schemas\RawProductForm;
use App\Filament\Resources\RawProductResource\Tables\RawProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class RawProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'الكتالوج';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'المواد الخام';

    protected static ?string $modelLabel = 'مادة خام';

    protected static ?string $pluralModelLabel = 'المواد الخام';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'raw');
    }

    public static function form(Schema $schema): Schema
    {
        return RawProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RawProductsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRawProducts::route('/'),
            'create' => Pages\CreateRawProduct::route('/create'),
            'view' => Pages\ViewRawProduct::route('/{record}'),
            'edit' => Pages\EditRawProduct::route('/{record}/edit'),
        ];
    }
}
