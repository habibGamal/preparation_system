<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawMaterialOutResource\Tables;

use App\Enums\MaterialEntranceStatus;
use App\Filament\Components\Actions\CloseRawMaterialOutAction;
use App\Models\RawMaterialOut;
use App\Services\RawMaterialOutService;
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

final class RawMaterialOutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم الإخراج')
                    ->sortable(),

                TextColumn::make('consumer.name')
                    ->label('المستهلك')
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

                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('عدد الأصناف'),

                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('closed_at')
                    ->label('تاريخ الإغلاق')
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
                    ->options(MaterialEntranceStatus::class),

                SelectFilter::make('consumer')
                    ->relationship('consumer', 'name')
                    ->searchable()
                    ->preload()
                    ->label('المستهلك'),
            ])
            ->recordActions([
                CloseRawMaterialOutAction::make()
                    ->label('إغلاق'),

                Action::make('clone')
                    ->label('استنساخ')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('استنساخ الإخراج')
                    ->modalDescription('هل أنت متأكد من استنساخ هذا الإخراج؟ سيتم إنشاء نسخة جديدة بحالة مسودة.')
                    ->modalSubmitActionLabel('استنساخ')
                    ->action(function (RawMaterialOut $record) {
                        app(RawMaterialOutService::class)->clone($record);

                        Notification::make()
                            ->success()
                            ->title('تم استنساخ الإخراج')
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
                            return $records->filter(fn (RawMaterialOut $record) => $record->status === MaterialEntranceStatus::Draft
                            );
                        }),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(fn (RawMaterialOut $record) => $record->status === MaterialEntranceStatus::Draft
            )
            ->defaultSort('created_at', 'desc');
    }
}
