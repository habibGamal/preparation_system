<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawProductResource\Tables;

use App\Enums\ProductUnit;
use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class RawProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('الفئة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('barcode')
                    ->label('الباركود')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('unit')
                    ->label('الوحدة')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('cost')
                    ->label('التكلفة')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('min_stock')
                    ->label('الحد الأدنى للمخزون')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('inventory.quantity')
                    ->label('المخزون')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('الفئة')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('unit')
                    ->label('الوحدة')
                    ->options(ProductUnit::class),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ProductImporter::class)
                    ->label('استيراد')
                    ->color('success'),
                ExportAction::make()
                    ->exporter(ProductExporter::class)
                    ->label('تصدير')
                    ->color('primary'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(ProductExporter::class)
                        ->label('تصدير المحدد'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
