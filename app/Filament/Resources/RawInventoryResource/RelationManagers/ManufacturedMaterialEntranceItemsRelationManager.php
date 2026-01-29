<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawInventoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ManufacturedMaterialEntranceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'manufacturedMaterialEntranceItems';

    protected static ?string $title = 'إدخالات المصنعات';

    protected static ?string $modelLabel = 'إدخال مصنعات';

    protected static ?string $pluralModelLabel = 'إدخالات المصنعات';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('manufacturedMaterialEntrance.id')
                    ->label('رقم الإدخال')
                    ->sortable(),
                TextColumn::make('manufacturedMaterialEntrance.supplier.name')
                    ->label('المورد')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('manufacturedMaterialEntrance.status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('manufacturedMaterialEntrance.created_at')
                    ->label('تاريخ الإدخال')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('manufacturedMaterialEntrance.created_at', 'desc');
    }
}
