<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ProductType;
use App\Filament\Resources\RawWasteResource\Pages;
use App\Filament\Resources\RawWasteResource\Schemas\RawWasteForm;
use App\Filament\Resources\RawWasteResource\Tables\RawWastesTable;
use App\Models\Waste;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class RawWasteResource extends Resource
{
    protected static ?string $model = Waste::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trash';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة المخزون الخام';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationLabel = 'تالف المواد الخام';

    protected static ?string $modelLabel = 'تالف مواد خام';

    protected static ?string $pluralModelLabel = 'تالف المواد الخام';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', ProductType::Raw->value);
    }

    public static function form(Schema $schema): Schema
    {
        return RawWasteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RawWastesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRawWastes::route('/'),
            'create' => Pages\CreateRawWaste::route('/create'),
            'view' => Pages\ViewRawWaste::route('/{record}'),
            'edit' => Pages\EditRawWaste::route('/{record}/edit'),
        ];
    }
}
