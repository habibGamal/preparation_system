<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturingRecipeResource\Tables;

use App\Services\RecipeCalculationService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class ManufacturingRecipesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم الوصفة')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('اسم الوصفة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('المنتج المصنع')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_auto_calculated')
                    ->label('محسوبة تلقائياً')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('calculated_from_orders_count')
                    ->label('من أوامر')
                    ->sortable()
                    ->placeholder('-')
                    ->formatStateUsing(fn (?int $state) => $state ? $state.' أمر' : '-'),

                TextColumn::make('last_calculated_at')
                    ->label('آخر حساب')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('expected_output_quantity')
                    ->label('الكمية المتوقعة')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('عدد المواد الخام'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_auto_calculated')
                    ->label('محسوبة تلقائياً')
                    ->trueLabel('نعم')
                    ->falseLabel('لا')
                    ->placeholder('الكل'),

                SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->label('المنتج المصنع'),
            ])
            ->recordActions([
                Action::make('recalculate')
                    ->label('إعادة الحساب')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('إعادة حساب الوصفة')
                    ->modalDescription('سيتم إعادة حساب هذه الوصفة من أوامر التصنيع المكتملة. هل أنت متأكد؟')
                    ->modalSubmitActionLabel('إعادة الحساب')
                    ->action(function ($record) {
                        $recipe = app(RecipeCalculationService::class)
                            ->calculateRecipeFromOrders($record->product);

                        if ($recipe) {
                            Notification::make()
                                ->success()
                                ->title('تم إعادة حساب الوصفة')
                                ->body("تم الحساب من {$recipe->calculated_from_orders_count} أمر تصنيع")
                                ->send();
                        } else {
                            Notification::make()
                                ->warning()
                                ->title('لا توجد أوامر كافية')
                                ->body('الحد الأدنى المطلوب هو '.config('manufacturing.minimum_orders_for_recipe').' أوامر')
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->is_auto_calculated),

                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
