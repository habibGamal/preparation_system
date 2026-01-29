<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedInventoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class RawMaterialEntranceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'rawMaterialEntranceItems';

    protected static ?string $title = 'إدخالات المواد الخام';

    protected static ?string $modelLabel = 'إدخال مواد خام';

    protected static ?string $pluralModelLabel = 'إدخالات المواد الخام';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rawMaterialEntrance.id')
                    ->label('رقم الإدخال')
                    ->sortable(),
                TextColumn::make('rawMaterialEntrance.supplier.name')
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
                TextColumn::make('rawMaterialEntrance.status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('rawMaterialEntrance.created_at')
                    ->label('تاريخ الإدخال')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('rawMaterialEntrance.created_at', 'desc');
    }
}
