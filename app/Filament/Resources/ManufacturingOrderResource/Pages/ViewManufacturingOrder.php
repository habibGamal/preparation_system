<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturingOrderResource\Pages;

use App\Filament\Resources\ManufacturingOrderResource;
use App\Services\RecipeCalculationService;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

final class ViewManufacturingOrder extends ViewRecord
{
    protected static string $resource = ManufacturingOrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        $recipeService = app(RecipeCalculationService::class);

        return $schema
            ->columns(3)
            ->components([
                Section::make('معلومات الأمر')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('product.name')
                            ->label('المنتج المصنع'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge(),
                        TextEntry::make('output_quantity')
                            ->label('كمية الإنتاج')
                            ->numeric(decimalPlaces: 2),
                        TextEntry::make('user.name')
                            ->label('المستخدم'),
                        TextEntry::make('completed_at')
                            ->label('تاريخ الإتمام')
                            ->dateTime()
                            ->placeholder('غير مكتمل'),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull()
                            ->placeholder('لا توجد ملاحظات'),
                    ]),

                Section::make('المواد الخام المستخدمة')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->table([
                                TableColumn::make('المنتج'),
                                TableColumn::make('الكمية المستخدمة'),
                                TableColumn::make('الكمية المتوقعة'),
                                TableColumn::make('الانحراف %'),
                                TableColumn::make('الحالة'),
                            ])
                            ->schema([
                                TextEntry::make('product.name'),
                                TextEntry::make('quantity')
                                    ->numeric(decimalPlaces: 2)
                                    ->suffix(fn ($record) => ' '.$record->product->unit->getLabel()),
                                TextEntry::make('expected_quantity')
                                    ->label('الكمية المتوقعة')
                                    ->state(function ($record) use ($recipeService): string {
                                        $order = $this->getRecord();
                                        $recipe = $recipeService->getRecipeForProduct($order->product);

                                        if (! $recipe) {
                                            return '—';
                                        }

                                        $recipeItem = $recipe->items->firstWhere('product_id', $record->product_id);

                                        if (! $recipeItem) {
                                            return '0.00';
                                        }

                                        $expected = (float) $recipeItem->quantity * (float) $order->output_quantity;

                                        return number_format($expected, 2);
                                    })
                                    ->suffix(fn ($record) => ' '.$record->product->unit->getLabel()),
                                TextEntry::make('variance')
                                    ->label('الانحراف')
                                    ->state(function ($record) use ($recipeService): string {
                                        $order = $this->getRecord();
                                        $recipe = $recipeService->getRecipeForProduct($order->product);

                                        if (! $recipe) {
                                            return '—';
                                        }

                                        $recipeItem = $recipe->items->firstWhere('product_id', $record->product_id);

                                        if (! $recipeItem) {
                                            return '+100%';
                                        }

                                        $expected = (float) $recipeItem->quantity * (float) $order->output_quantity;
                                        $actual = (float) $record->quantity;

                                        if ($expected == 0) {
                                            return $actual > 0 ? '+100%' : '0%';
                                        }

                                        $variance = (($actual - $expected) / $expected) * 100;

                                        return ($variance > 0 ? '+' : '').number_format($variance, 1).'%';
                                    })
                                    ->color(function ($record) use ($recipeService): string {
                                        $order = $this->getRecord();
                                        $recipe = $recipeService->getRecipeForProduct($order->product);

                                        if (! $recipe) {
                                            return 'gray';
                                        }

                                        $recipeItem = $recipe->items->firstWhere('product_id', $record->product_id);

                                        if (! $recipeItem) {
                                            return 'warning';
                                        }

                                        $expected = (float) $recipeItem->quantity * (float) $order->output_quantity;
                                        $actual = (float) $record->quantity;

                                        if ($expected == 0) {
                                            return 'warning';
                                        }

                                        $variance = abs((($actual - $expected) / $expected) * 100);
                                        $threshold = config('manufacturing.variance_warning_threshold', 10.0);

                                        return $variance > $threshold ? 'danger' : 'success';
                                    })
                                    ->weight(FontWeight::Bold),
                                IconEntry::make('status_icon')
                                    ->label('')
                                    ->state(function ($record) use ($recipeService): bool {
                                        $order = $this->getRecord();
                                        $recipe = $recipeService->getRecipeForProduct($order->product);

                                        if (! $recipe) {
                                            return false;
                                        }

                                        $recipeItem = $recipe->items->firstWhere('product_id', $record->product_id);

                                        if (! $recipeItem) {
                                            return false;
                                        }

                                        $expected = (float) $recipeItem->quantity * (float) $order->output_quantity;
                                        $actual = (float) $record->quantity;

                                        if ($expected == 0) {
                                            return false;
                                        }

                                        $variance = abs((($actual - $expected) / $expected) * 100);
                                        $threshold = config('manufacturing.variance_warning_threshold', 10.0);

                                        return $variance <= $threshold;
                                    })
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-exclamation-triangle')
                                    ->trueColor('success')
                                    ->falseColor('warning'),
                            ]),
                    ]),

                Section::make('تحذيرات الانحراف')
                    ->columnSpanFull()
                    ->visible(fn () => count($recipeService->getVarianceFromRecipe($this->getRecord())) > 0)
                    ->schema([
                        RepeatableEntry::make('variance_warnings')
                            ->label('')
                            ->state(fn () => $recipeService->getVarianceFromRecipe($this->getRecord()))
                            ->table([
                                TableColumn::make('النوع'),
                                TableColumn::make('المنتج'),
                                TableColumn::make('المتوقع'),
                                TableColumn::make('الفعلي'),
                                TableColumn::make('الانحراف'),
                                TableColumn::make('الرسالة'),
                            ])
                            ->schema([
                                TextEntry::make('type')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'raw_material' => 'مادة خام',
                                        'missing_ingredient' => 'خام مفقود',
                                        'extra_ingredient' => 'خام إضافي',
                                        default => $state,
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'raw_material' => 'warning',
                                        'missing_ingredient' => 'danger',
                                        'extra_ingredient' => 'info',
                                        default => 'gray',
                                    }),
                                TextEntry::make('product'),
                                TextEntry::make('expected')
                                    ->numeric(decimalPlaces: 2),
                                TextEntry::make('actual')
                                    ->numeric(decimalPlaces: 2),
                                TextEntry::make('variance')
                                    ->suffix('%')
                                    ->color(fn (float $state): string => abs($state) > 10 ? 'danger' : 'warning')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('message')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
