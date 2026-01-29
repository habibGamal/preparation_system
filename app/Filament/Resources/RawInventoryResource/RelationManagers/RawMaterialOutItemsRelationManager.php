<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawInventoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class RawMaterialOutItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'rawMaterialOutItems';

    protected static ?string $title = 'إخراجات المواد الخام';

    protected static ?string $modelLabel = 'إخراج مواد خام';

    protected static ?string $pluralModelLabel = 'إخراجات المواد الخام';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rawMaterialOut.id')
                    ->label('رقم الإخراج')
                    ->sortable(),
                TextColumn::make('rawMaterialOut.consumer.name')
                    ->label('المستهلك')
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
                TextColumn::make('rawMaterialOut.status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('rawMaterialOut.created_at')
                    ->label('تاريخ الإخراج')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('rawMaterialOut.created_at', 'desc');
    }
}
