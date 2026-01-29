<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ManufacturedMaterialOutResource\Pages;
use App\Filament\Resources\ManufacturedMaterialOutResource\Schemas\ManufacturedMaterialOutForm;
use App\Filament\Resources\ManufacturedMaterialOutResource\Tables\ManufacturedMaterialOutsTable;
use App\Models\ManufacturedMaterialOut;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class ManufacturedMaterialOutResource extends Resource
{
    protected static ?string $model = ManufacturedMaterialOut::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة المخزون المصنع';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'إخراج المصنعات';

    protected static ?string $modelLabel = 'إخراج مصنعات';

    protected static ?string $pluralModelLabel = 'إخراجات المصنعات';

    public static function form(Schema $schema): Schema
    {
        return ManufacturedMaterialOutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManufacturedMaterialOutsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManufacturedMaterialOuts::route('/'),
            'create' => Pages\CreateManufacturedMaterialOut::route('/create'),
            'view' => Pages\ViewManufacturedMaterialOut::route('/{record}'),
            'edit' => Pages\EditManufacturedMaterialOut::route('/{record}/edit'),
        ];
    }
}
