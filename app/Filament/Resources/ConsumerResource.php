<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ConsumerResource\Pages;
use App\Filament\Resources\ConsumerResource\Schemas\ConsumerForm;
use App\Filament\Resources\ConsumerResource\Tables\ConsumersTable;
use App\Models\Consumer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class ConsumerResource extends Resource
{
    protected static ?string $model = Consumer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'جهات الاتصال';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'العملاء';

    protected static ?string $modelLabel = 'عميل';

    protected static ?string $pluralModelLabel = 'العملاء';

    public static function form(Schema $schema): Schema
    {
        return ConsumerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConsumersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsumers::route('/'),
            'create' => Pages\CreateConsumer::route('/create'),
            'view' => Pages\ViewConsumer::route('/{record}'),
            'edit' => Pages\EditConsumer::route('/{record}/edit'),
        ];
    }
}
