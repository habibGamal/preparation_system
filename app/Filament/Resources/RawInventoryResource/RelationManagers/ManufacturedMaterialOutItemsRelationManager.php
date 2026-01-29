<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawInventoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ManufacturedMaterialOutItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'manufacturedMaterialOutItems';

    protected static ?string $title = 'إخراجات المصنعات';

    protected static ?string $modelLabel = 'إخراج مصنعات';

    protected static ?string $pluralModelLabel = 'إخراجات المصنعات';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('manufacturedMaterialOut.id')
                    ->label('رقم الإخراج')
                    ->sortable(),
                TextColumn::make('manufacturedMaterialOut.consumer.name')
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
                TextColumn::make('manufacturedMaterialOut.status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('manufacturedMaterialOut.created_at')
                    ->label('تاريخ الإخراج')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('manufacturedMaterialOut.created_at', 'desc');
    }
}
