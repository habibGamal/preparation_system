<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ProductType;
use App\Filament\Resources\ManufacturedWasteResource\Pages;
use App\Filament\Resources\ManufacturedWasteResource\Schemas\ManufacturedWasteForm;
use App\Filament\Resources\ManufacturedWasteResource\Tables\ManufacturedWastesTable;
use App\Models\Waste;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ManufacturedWasteResource extends Resource
{
    protected static ?string $model = Waste::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trash';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة المخزون المصنع';

    protected static ?int $navigationSort = 13;

    protected static ?string $navigationLabel = 'تالف المصنعات';

    protected static ?string $modelLabel = 'تالف مصنعات';

    protected static ?string $pluralModelLabel = 'تالف المصنعات';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', ProductType::Manufactured->value);
    }

    public static function form(Schema $schema): Schema
    {
        return ManufacturedWasteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManufacturedWastesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManufacturedWastes::route('/'),
            'create' => Pages\CreateManufacturedWaste::route('/create'),
            'view' => Pages\ViewManufacturedWaste::route('/{record}'),
            'edit' => Pages\EditManufacturedWaste::route('/{record}/edit'),
        ];
    }
}
