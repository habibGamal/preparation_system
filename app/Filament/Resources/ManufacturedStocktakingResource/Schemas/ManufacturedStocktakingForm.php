<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedStocktakingResource\Schemas;

use App\Enums\ProductType;
use App\Models\Product;
use App\Models\Stocktaking;
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

final class ManufacturedStocktakingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الجرد')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(fn () => Auth::id()),

                        Hidden::make('product_type')
                            ->default(ProductType::Manufactured->value),

                        Placeholder::make('user_name')
                            ->label('المستخدم')
                            ->content(fn (?Stocktaking $record) => $record?->user?->name ?? Auth::user()?->name),

                        Placeholder::make('closed_at_display')
                            ->label('تاريخ الإغلاق')
                            ->content(fn (?Stocktaking $record) => $record?->closed_at?->format('Y-m-d H:i') ?? 'لم يتم الإغلاق')
                            ->visible(fn (string $operation) => $operation === 'edit' || $operation === 'view'),

                        TextInput::make('total')
                            ->label('إجمالي الفرق (ج.م)')
                            ->numeric()
                            ->prefix('ج.م')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(0)
                            ->helperText('يتم حساب الإجمالي تلقائياً من قيمة الفروقات'),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(
                                fn (string $operation, ?Stocktaking $record) => $operation === 'edit' && $record?->isClosed()
                            ),
                    ])
                    ->columns(2),

                Section::make('المنتجات المصنعة')
                    ->schema([
                        Repeater::make('items')
                            ->label('المنتجات المصنعة')
                            ->relationship()
                            ->dehydrated(true)
                            ->live()
                            ->table([
                                TableColumn::make('المنتج')->width('25%'),
                                TableColumn::make('الكمية بالنظام'),
                                TableColumn::make('الكمية الفعلية'),
                                TableColumn::make('الفرق'),
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
                                    ->getOptionLabelUsing(fn ($value): ?string => Product::query()->find($value)?->name)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $product = Product::with('inventory')->find($state);
                                            if ($product) {
                                                // Set stock quantity from inventory
                                                $stockQuantity = $product->inventory?->quantity ?? 0;
                                                $set('stock_quantity', $stockQuantity);
                                                $set('price', $product->price);

                                                // Calculate variance and total
                                                $realQuantity = (float) ($get('real_quantity') ?? 0);
                                                $variance = $realQuantity - $stockQuantity;
                                                $total = $variance * (float) $product->price;
                                                $set('total', $total);
                                            }
                                        }
                                    })
                                    ->afterStateUpdatedJs(<<<'JS'
                                        let allItems = Object.values($get('../../items') || {});
                                        let overallTotal = 0;
                                        for (let item of allItems) {
                                            overallTotal += parseFloat(item.total) || 0;
                                        }
                                        $set('../../total', overallTotal);
                                        JS),

                                TextInput::make('stock_quantity')
                                    ->label('الكمية بالنظام')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->helperText('يتم جلبها تلقائياً من المخزون'),

                                TextInput::make('real_quantity')
                                    ->label('الكمية الفعلية')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdatedJs(<<<'JS'
                                        const stockQty = parseFloat($get('stock_quantity') || 0);
                                        const realQty = parseFloat($state || 0);
                                        const variance = realQty - stockQty;
                                        const price = parseFloat($get('price') || 0);
                                        $set('total', (variance * price).toFixed(2));

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
                                        const stockQty = parseFloat($get('stock_quantity') || 0);
                                        const realQty = parseFloat($get('real_quantity') || 0);
                                        const variance = realQty - stockQty;
                                        const price = parseFloat($state || 0);
                                        $set('total', (variance * price).toFixed(2));

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
                                    ->required()
                                    ->helperText('الفرق × السعر'),
                            ])
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->addActionLabel('إضافة منتج مصنع')
                            ->disabled(
                                fn (string $operation, ?Stocktaking $record) => $operation === 'edit' && $record?->isClosed()
                            ),
                    ]),
            ])->columns(1);
    }
}
