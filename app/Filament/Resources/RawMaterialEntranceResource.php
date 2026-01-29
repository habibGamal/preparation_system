<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RawMaterialEntranceResource\Pages;
use App\Filament\Resources\RawMaterialEntranceResource\Schemas\RawMaterialEntranceForm;
use App\Filament\Resources\RawMaterialEntranceResource\Tables\RawMaterialEntrancesTable;
use App\Models\RawMaterialEntrance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class RawMaterialEntranceResource extends Resource
{
    protected static ?string $model = RawMaterialEntrance::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة المخزون الخام';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'إدخال المواد الخام';

    protected static ?string $modelLabel = 'إدخال مواد خام';

    protected static ?string $pluralModelLabel = 'إدخالات المواد الخام';

    public static function form(Schema $schema): Schema
    {
        return RawMaterialEntranceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RawMaterialEntrancesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRawMaterialEntrances::route('/'),
            'create' => Pages\CreateRawMaterialEntrance::route('/create'),
            'view' => Pages\ViewRawMaterialEntrance::route('/{record}'),
            'edit' => Pages\EditRawMaterialEntrance::route('/{record}/edit'),
        ];
    }
}
