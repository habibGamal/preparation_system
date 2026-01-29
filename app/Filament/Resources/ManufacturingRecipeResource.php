<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ManufacturingRecipeResource\Pages;
use App\Filament\Resources\ManufacturingRecipeResource\Schemas\ManufacturingRecipeForm;
use App\Filament\Resources\ManufacturingRecipeResource\Tables\ManufacturingRecipesTable;
use App\Models\ManufacturingRecipe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class ManufacturingRecipeResource extends Resource
{
    protected static ?string $model = ManufacturingRecipe::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|UnitEnum|null $navigationGroup = 'التصنيع';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'وصفات التصنيع';

    protected static ?string $modelLabel = 'وصفة تصنيع';

    protected static ?string $pluralModelLabel = 'وصفات التصنيع';

    public static function form(Schema $schema): Schema
    {
        return ManufacturingRecipeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManufacturingRecipesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManufacturingRecipes::route('/'),
            'view' => Pages\ViewManufacturingRecipe::route('/{record}'),
        ];
    }
}
