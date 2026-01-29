<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawWasteResource\Schemas;

use App\Enums\ProductType;
use App\Models\Product;
use App\Models\Waste;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

final class RawWasteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات التالف')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(fn () => Auth::id()),

                        Hidden::make('type')
                            ->default(ProductType::Raw->value),

                        Placeholder::make('user_name')
                            ->label('المستخدم')
                            ->content(fn (?Waste $record) => $record?->user?->name ?? Auth::user()?->name),

                        Placeholder::make('closed_at_display')
                            ->label('تاريخ الإغلاق')
                            ->content(fn (?Waste $record) => $record?->closed_at?->format('Y-m-d H:i') ?? 'لم يتم الإغلاق')
                            ->visible(fn (string $operation) => $operation === 'edit' || $operation === 'view'),

                        TextInput::make('total')
                            ->label('إجمالي التالف (ج.م)')
                            ->numeric()
                            ->prefix('ج.م')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(0)
                            ->helperText('يتم حساب الإجمالي تلقائياً من قيمة الأصناف التالفة'),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(
                                fn (string $operation, ?Waste $record) => $operation === 'edit' && $record?->isClosed()
                            ),
                    ])
                    ->columns(2),

                Section::make('الأصناف التالفة')
                    ->schema([
                        Repeater::make('wastedItems')
                            ->label('الأصناف')
                            ->relationship()
                            ->dehydrated(true)
                            ->live()
                            ->table([
                                TableColumn::make('المنتج')->width('30%'),
                                TableColumn::make('الكمية بالمخزون'),
                                TableColumn::make('الكمية التالفة'),
                                TableColumn::make('السعر'),
                                TableColumn::make('الإجمالي'),
                            ])
                            ->schema([
                                Select::make('product_id')
                                    ->label('المنتج')
                                    ->relationship(
                                        name: 'product',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->where('type', ProductType::Raw->value)
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $product = Product::with('inventory')->find($state);
                                            if ($product) {
                                                // Set stock quantity from inventory
                                                $stockQuantity = $product->inventory?->quantity ?? 0;
                                                $set('stock_quantity', $stockQuantity);
                                                $set('price', $product->cost);

                                                // Calculate total
                                                $quantity = (float) ($get('quantity') ?? 0);
                                                $total = $quantity * (float) $product->cost;
                                                $set('total', $total);
                                            }
                                        }
                                    })
                                    ->afterStateUpdatedJs(<<<'JS'
                                        let allItems = Object.values($get('../../wastedItems') || {});
                                        let overallTotal = 0;
                                        for (let item of allItems) {
                                            overallTotal += parseFloat(item.total) || 0;
                                        }
                                        $set('../../total', overallTotal);
                                        JS),

                                TextInput::make('stock_quantity')
                                    ->label('الكمية بالمخزون')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('للعرض فقط - يتم جلبها من المخزون'),

                                TextInput::make('quantity')
                                    ->label('الكمية التالفة')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdatedJs(<<<'JS'
                                        const quantity = parseFloat($state || 0);
                                        const price = parseFloat($get('price') || 0);
                                        $set('total', (quantity * price).toFixed(2));

                                        let allItems = Object.values($get('../../wastedItems') || {});
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

                                        let allItems = Object.values($get('../../wastedItems') || {});
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
                                    ->required()
                                    ->helperText('الكمية × السعر'),
                            ])
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->addActionLabel('إضافة صنف')
                            ->disabled(
                                fn (string $operation, ?Waste $record) => $operation === 'edit' && $record?->isClosed()
                            ),
                    ]),
            ])->columns(1);
    }
}
