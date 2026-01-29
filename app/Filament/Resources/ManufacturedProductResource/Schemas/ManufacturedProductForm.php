<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedProductResource\Schemas;

use App\Enums\ProductType;
use App\Enums\ProductUnit;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ManufacturedProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),

                        Select::make('category_id')
                            ->label('الفئة')
                            ->relationship(name: 'category', titleAttribute: 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('الاسم')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        TextInput::make('barcode')
                            ->label('الباركود')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('التصنيف')
                    ->schema([
                        Hidden::make('type')
                            ->default(ProductType::Manufactured),

                        Select::make('unit')
                            ->label('الوحدة')
                            ->options(ProductUnit::class)
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('الأسعار والمخزون')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->label('سعر البيع')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->suffix('ج.م')
                                    ->required(),

                                TextInput::make('cost')
                                    ->label('سعر التكلفة')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->suffix('ج.م')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_stock')
                                    ->label('الحد الأدنى للمخزون')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),

                                TextInput::make('avg_purchase_quantity')
                                    ->label('متوسط كمية الشراء')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
