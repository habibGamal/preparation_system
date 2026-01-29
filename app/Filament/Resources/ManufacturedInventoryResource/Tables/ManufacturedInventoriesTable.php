<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedInventoryResource\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ManufacturedInventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.category.name')
                    ->label('الفئة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.unit')
                    ->label('الوحدة')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => $record->quantity < $record->product->min_stock ? 'danger' : 'success'),

                TextColumn::make('product.min_stock')
                    ->label('الحد الأدنى')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product.category')
                    ->relationship('product.category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('الفئة'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('product.name');
    }
}
