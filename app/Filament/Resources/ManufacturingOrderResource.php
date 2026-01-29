<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ManufacturingOrderResource\Pages;
use App\Filament\Resources\ManufacturingOrderResource\Schemas\ManufacturingOrderForm;
use App\Filament\Resources\ManufacturingOrderResource\Tables\ManufacturingOrdersTable;
use App\Models\ManufacturingOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class ManufacturingOrderResource extends Resource
{
    protected static ?string $model = ManufacturingOrder::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'التصنيع';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'أذون التصنيع';

    protected static ?string $modelLabel = 'إذن تصنيع';

    protected static ?string $pluralModelLabel = 'أذون التصنيع';

    public static function form(Schema $schema): Schema
    {
        return ManufacturingOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManufacturingOrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManufacturingOrders::route('/'),
            'create' => Pages\CreateManufacturingOrder::route('/create'),
            'view' => Pages\ViewManufacturingOrder::route('/{record}'),
            'edit' => Pages\EditManufacturingOrder::route('/{record}/edit'),
        ];
    }
}
