<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ManufacturedMaterialEntranceResource\Pages;
use App\Filament\Resources\ManufacturedMaterialEntranceResource\Schemas\ManufacturedMaterialEntranceForm;
use App\Filament\Resources\ManufacturedMaterialEntranceResource\Tables\ManufacturedMaterialEntrancesTable;
use App\Models\ManufacturedMaterialEntrance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class ManufacturedMaterialEntranceResource extends Resource
{
    protected static ?string $model = ManufacturedMaterialEntrance::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة المخزون المصنع';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'إدخال المصنعات';

    protected static ?string $modelLabel = 'إدخال مصنعات';

    protected static ?string $pluralModelLabel = 'إدخالات المصنعات';

    public static function form(Schema $schema): Schema
    {
        return ManufacturedMaterialEntranceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManufacturedMaterialEntrancesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManufacturedMaterialEntrances::route('/'),
            'create' => Pages\CreateManufacturedMaterialEntrance::route('/create'),
            'view' => Pages\ViewManufacturedMaterialEntrance::route('/{record}'),
            'edit' => Pages\EditManufacturedMaterialEntrance::route('/{record}/edit'),
        ];
    }
}
