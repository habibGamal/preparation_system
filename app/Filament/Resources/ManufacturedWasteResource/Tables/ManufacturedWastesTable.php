<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedWasteResource\Tables;

use App\Filament\Components\Actions\CloseWasteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ManufacturedWastesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم التالف')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('wastedItems_count')
                    ->counts('wastedItems')
                    ->label('عدد الأصناف')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('إجمالي التالف')
                    ->money('EGP')
                    ->sortable()
                    ->color('danger'),

                IconColumn::make('closed_at')
                    ->label('مغلق؟')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),

                TextColumn::make('closed_at')
                    ->label('تاريخ الإغلاق')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->placeholder('لم يتم الإغلاق'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('closed')
                    ->label('مغلق')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('closed_at')),

                Filter::make('open')
                    ->label('مفتوح')
                    ->query(fn (Builder $query): Builder => $query->whereNull('closed_at')),
            ])
            ->recordActions([
                CloseWasteAction::make(),
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => ! $record->isClosed()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->isClosed()) {
                                    throw new \Exception('لا يمكن حذف التالف المغلق');
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
