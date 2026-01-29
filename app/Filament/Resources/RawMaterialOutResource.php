<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RawMaterialOutResource\Pages;
use App\Filament\Resources\RawMaterialOutResource\Schemas\RawMaterialOutForm;
use App\Filament\Resources\RawMaterialOutResource\Tables\RawMaterialOutsTable;
use App\Models\RawMaterialOut;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class RawMaterialOutResource extends Resource
{
    protected static ?string $model = RawMaterialOut::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة المخزون الخام';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'إخراج المواد الخام';

    protected static ?string $modelLabel = 'إخراج مواد خام';

    protected static ?string $pluralModelLabel = 'إخراجات المواد الخام';

    public static function form(Schema $schema): Schema
    {
        return RawMaterialOutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RawMaterialOutsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRawMaterialOuts::route('/'),
            'create' => Pages\CreateRawMaterialOut::route('/create'),
            'view' => Pages\ViewRawMaterialOut::route('/{record}'),
            'edit' => Pages\EditRawMaterialOut::route('/{record}/edit'),
        ];
    }
}
