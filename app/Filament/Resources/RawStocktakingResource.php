<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ProductType;
use App\Filament\Resources\RawStocktakingResource\Pages;
use App\Filament\Resources\RawStocktakingResource\Schemas\RawStocktakingForm;
use App\Filament\Resources\RawStocktakingResource\Tables\RawStocktakingsTable;
use App\Models\Stocktaking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class RawStocktakingResource extends Resource
{
    protected static ?string $model = Stocktaking::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة المخزون الخام';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'جرد المواد الخام';

    protected static ?string $modelLabel = 'جرد مواد خام';

    protected static ?string $pluralModelLabel = 'جرد المواد الخام';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('product_type', ProductType::Raw->value);
    }

    public static function form(Schema $schema): Schema
    {
        return RawStocktakingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RawStocktakingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRawStocktakings::route('/'),
            'create' => Pages\CreateRawStocktaking::route('/create'),
            'view' => Pages\ViewRawStocktaking::route('/{record}'),
            'edit' => Pages\EditRawStocktaking::route('/{record}/edit'),
        ];
    }
}
