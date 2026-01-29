<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturingOrderResource\Tables;

use App\Enums\ManufacturingOrderStatus;
use App\Filament\Components\Actions\CompleteManufacturingOrderAction;
use App\Models\ManufacturingOrder;
use App\Services\ManufacturingOrderService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ManufacturingOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم الإذن')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('المنتج المصنع')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('أنشئ بواسطة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('output_quantity')
                    ->label('كمية الناتج')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('عدد المواد الخام'),

                TextColumn::make('completed_at')
                    ->label('تاريخ الإكمال')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(ManufacturingOrderStatus::class),

                SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->label('المنتج المصنع'),
            ])
            ->recordActions([
                CompleteManufacturingOrderAction::make()
                    ->label('إكمال'),

                Action::make('clone')
                    ->label('استنساخ')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('استنساخ إذن التصنيع')
                    ->modalDescription('هل أنت متأكد من استنساخ هذا الإذن؟ سيتم إنشاء نسخة جديدة بحالة مسودة.')
                    ->modalSubmitActionLabel('استنساخ')
                    ->action(function (ManufacturingOrder $record) {
                        app(ManufacturingOrderService::class)->clone($record);

                        Notification::make()
                            ->success()
                            ->title('تم استنساخ الإذن')
                            ->body('تم إنشاء نسخة جديدة بنجاح')
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            return $records->filter(fn (ManufacturingOrder $record) => $record->status === ManufacturingOrderStatus::Draft
                            );
                        }),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(fn (ManufacturingOrder $record) => $record->status === ManufacturingOrderStatus::Draft
            )
            ->defaultSort('created_at', 'desc');
    }
}
