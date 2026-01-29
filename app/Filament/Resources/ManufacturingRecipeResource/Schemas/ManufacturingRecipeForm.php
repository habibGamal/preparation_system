<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturingRecipeResource\Schemas;

use App\Enums\ProductType;
use App\Models\ManufacturingRecipe;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ManufacturingRecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الوصفة')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الوصفة')
                            ->disabled(),

                        Select::make('product_id')
                            ->label('المنتج المصنع')
                            ->relationship(
                                name: 'product',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('type', ProductType::Manufactured->value)
                            )
                            ->disabled(),

                        TextInput::make('expected_output_quantity')
                            ->label('متوسط الكمية المنتجة')
                            ->disabled(),

                        Placeholder::make('calculation_info')
                            ->label('معلومات الحساب')
                            ->content(fn (?ManufacturingRecipe $record) => $record?->is_auto_calculated
                                ? "محسوبة تلقائياً من {$record->calculated_from_orders_count} أمر تصنيع (آخر الأوامر المكتملة)"
                                : 'غير محسوبة تلقائياً'
                            ),

                        Placeholder::make('last_calculated')
                            ->label('آخر تحديث')
                            ->content(fn (?ManufacturingRecipe $record) => $record?->last_calculated_at?->format('Y-m-d H:i') ?? '-'),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('المواد الخام (معدل الاستهلاك لكل وحدة منتج)')
                    ->schema([
                        Repeater::make('items')
                            ->label('المواد الخام')
                            ->relationship()
                            ->table([
                                TableColumn::make('المادة الخام')->width('40%'),
                                TableColumn::make('الكمية / وحدة'),
                                TableColumn::make('نسبة الاستخدام'),
                            ])
                            ->schema([
                                Select::make('product_id')
                                    ->label('المادة الخام')
                                    ->relationship(
                                        name: 'product',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->where('type', ProductType::Raw->value)
                                    )
                                    ->disabled(),

                                TextInput::make('quantity')
                                    ->label('الكمية / وحدة')
                                    ->disabled()
                                    ->suffix('لكل 1 وحدة منتج'),

                                Placeholder::make('usage_frequency_display')
                                    ->label('نسبة الاستخدام')
                                    ->content(fn ($record) => $record?->usage_frequency
                                        ? round((float) $record->usage_frequency).'%'
                                        : '-'
                                    ),
                            ])
                            ->disabled()
                            ->deletable(false)
                            ->addable(false)
                            ->reorderable(false),
                    ]),
            ])->columns(1);
    }
}
