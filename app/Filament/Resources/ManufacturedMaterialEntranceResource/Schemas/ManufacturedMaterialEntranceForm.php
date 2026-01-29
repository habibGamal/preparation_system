<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedMaterialEntranceResource\Schemas;

use App\Enums\MaterialEntranceStatus;
use App\Enums\ProductType;
use App\Filament\Components\Forms\ManufacturedProductSelector;
use App\Models\ManufacturedMaterialEntrance;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

final class ManufacturedMaterialEntranceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الإدخال')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(fn () => Auth::id()),

                        Select::make('supplier_id')
                            ->label('المورد')
                            ->relationship(name: 'supplier', titleAttribute: 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('الاسم')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('رقم الهاتف')
                                    ->tel()
                                    ->maxLength(255),
                            ])
                            ->disabled(
                                fn (string $operation, ?ManufacturedMaterialEntrance $record) => $operation === 'edit' && $record?->status === MaterialEntranceStatus::Closed
                            ),

                        Hidden::make('status')
                            ->default(MaterialEntranceStatus::Draft),

                        Placeholder::make('status_display')
                            ->label('الحالة')
                            ->content(fn (?ManufacturedMaterialEntrance $record) => $record?->status?->getLabel() ?? MaterialEntranceStatus::Draft->getLabel()),

                        Placeholder::make('closed_at_display')
                            ->label('تاريخ الإغلاق')
                            ->content(fn (?ManufacturedMaterialEntrance $record) => $record?->closed_at?->format('Y-m-d H:i') ?? '-')
                            ->visible(fn (?ManufacturedMaterialEntrance $record) => $record?->status === MaterialEntranceStatus::Closed),

                        TextInput::make('total')
                            ->label('إجمالي الفاتورة (ج.م)')
                            ->numeric()
                            ->prefix('ج.م')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(0),
                    ])
                    ->columns(2),

                Section::make('الأصناف')
                    ->schema([
                        ManufacturedProductSelector::make('product_selector')
                            ->disabled(
                                fn (string $operation, ?ManufacturedMaterialEntrance $record) => $operation === 'edit' && $record?->status === MaterialEntranceStatus::Closed
                            ),

                        Repeater::make('items')
                            ->label('الأصناف')
                            ->relationship()
                            ->dehydrated(true)
                            ->live()
                            ->table([
                                TableColumn::make('المنتج')->width('30%'),
                                TableColumn::make('الكمية'),
                                TableColumn::make('السعر'),
                                TableColumn::make('الإجمالي'),
                            ])
                            ->schema([
                                Select::make('product_id')
                                    ->label('المنتج')
                                    ->relationship(
                                        name: 'product',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->where('type', ProductType::Manufactured->value)
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->getOptionLabelUsing(fn ($value) => \App\Models\Product::find($value)?->name)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $product = \App\Models\Product::find($state);
                                            if ($product) {
                                                $set('price', $product->cost);
                                            }
                                        }
                                    })
                                    ->afterStateUpdatedJs(<<<'JS'
                                        const quantity = parseFloat($get('quantity') || 0);
                                        const price = parseFloat($get('price') || 0);
                                        $set('total', (quantity * price).toFixed(2));
                                        let allItems = Object.values($get('../../items') || {});
                                        let overallTotal = 0;
                                        for (let item of allItems) {
                                            overallTotal += parseFloat(item.total) || 0;
                                        }
                                        $set('../../total', overallTotal);
                                        JS),

                                TextInput::make('quantity')
                                    ->label('الكمية')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdatedJs(<<<'JS'
                                        const quantity = parseFloat($state || 0);
                                        const price = parseFloat($get('price') || 0);
                                        $set('total', (quantity * price).toFixed(2));
                                        let allItems = Object.values($get('../../items') || {});
                                        let overallTotal = 0;
                                        for (let item of allItems) {
                                            overallTotal += parseFloat(item.total) || 0;
                                        }
                                        $set('../../total', overallTotal);
                                        JS),

                                TextInput::make('price')
                                    ->label('السعر')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->suffix('ج.م')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdatedJs(<<<'JS'
                                        const quantity = parseFloat($get('quantity') || 0);
                                        const price = parseFloat($state || 0);
                                        $set('total', (quantity * price).toFixed(2));
                                        let allItems = Object.values($get('../../items') || {});
                                        let overallTotal = 0;
                                        for (let item of allItems) {
                                            overallTotal += parseFloat(item.total) || 0;
                                        }
                                        $set('../../total', overallTotal);
                                        JS),

                                TextInput::make('total')
                                    ->label('الإجمالي')
                                    ->numeric()
                                    ->suffix('ج.م')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                            ])
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->addActionLabel('إضافة صنف')
                            ->disabled(
                                fn (string $operation, ?ManufacturedMaterialEntrance $record) => $operation === 'edit' && $record?->status === MaterialEntranceStatus::Closed
                            ),
                    ]),
            ])->columns(1);
    }
}
