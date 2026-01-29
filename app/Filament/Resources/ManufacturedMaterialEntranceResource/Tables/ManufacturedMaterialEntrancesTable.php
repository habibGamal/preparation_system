<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedMaterialEntranceResource\Tables;

use App\Enums\MaterialEntranceStatus;
use App\Filament\Components\Actions\CloseManufacturedMaterialEntranceAction;
use App\Models\ManufacturedMaterialEntrance;
use App\Services\ManufacturedMaterialEntranceService;
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

final class ManufacturedMaterialEntrancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم الإدخال')
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('المورد')
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

                SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->label('المورد'),
            ])
            ->recordActions([
                CloseManufacturedMaterialEntranceAction::make()
                    ->label('إغلاق'),

                Action::make('clone')
                    ->label('استنساخ')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('استنساخ الإدخال')
                    ->modalDescription('هل أنت متأكد من استنساخ هذا الإدخال؟ سيتم إنشاء نسخة جديدة بحالة مسودة.')
                    ->modalSubmitActionLabel('استنساخ')
                    ->action(function (ManufacturedMaterialEntrance $record) {
                        app(ManufacturedMaterialEntranceService::class)->clone($record);

                        Notification::make()
                            ->success()
                            ->title('تم استنساخ الإدخال')
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
                            return $records->filter(fn (ManufacturedMaterialEntrance $record) => $record->status === MaterialEntranceStatus::Draft
                            );
                        }),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(fn (ManufacturedMaterialEntrance $record) => $record->status === MaterialEntranceStatus::Draft
            )
            ->defaultSort('created_at', 'desc');
    }
}
