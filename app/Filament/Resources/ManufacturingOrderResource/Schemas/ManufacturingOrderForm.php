<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturingOrderResource\Schemas;

use App\Enums\ManufacturingOrderStatus;
use App\Enums\ProductType;
use App\Models\ManufacturingOrder;
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

final class ManufacturingOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات إذن التصنيع')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(fn () => Auth::id()),

                        Hidden::make('status')
                            ->default(ManufacturingOrderStatus::Draft),

                        Placeholder::make('status_display')
                            ->label('الحالة')
                            ->content(fn (?ManufacturingOrder $record) => $record?->status?->getLabel() ?? ManufacturingOrderStatus::Draft->getLabel()),

                        Select::make('product_id')
                            ->label('المنتج المصنع')
                            ->relationship(
                                name: 'product',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('type', ProductType::Manufactured->value)
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(
                                fn (string $operation, ?ManufacturingOrder $record) => $operation === 'edit' && $record?->status === ManufacturingOrderStatus::Completed
                            ),

                        TextInput::make('output_quantity')
                            ->label('كمية الناتج')
                            ->numeric()
                            ->default(1)
                            ->minValue(0.01)
                            ->required()
                            ->disabled(
                                fn (string $operation, ?ManufacturingOrder $record) => $operation === 'edit' && $record?->status === ManufacturingOrderStatus::Completed
                            ),

                        Placeholder::make('completed_at_display')
                            ->label('تاريخ الإكمال')
                            ->content(fn (?ManufacturingOrder $record) => $record?->completed_at?->format('Y-m-d H:i') ?? '-')
                            ->visible(fn (?ManufacturingOrder $record) => $record?->status === ManufacturingOrderStatus::Completed),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(
                                fn (string $operation, ?ManufacturingOrder $record) => $operation === 'edit' && $record?->status === ManufacturingOrderStatus::Completed
                            ),
                    ])
                    ->columns(2),

                Section::make('المواد الخام المستخدمة')
                    ->schema([
                        Repeater::make('items')
                            ->label('المواد الخام')
                            ->relationship()
                            ->table([
                                TableColumn::make('المادة الخام')->width('50%'),
                                TableColumn::make('الكمية'),
                            ])
                            ->schema([
                                Select::make('product_id')
                                    ->label('المادة الخام')
                                    ->relationship(
                                        name: 'product',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->where('type', ProductType::Raw->value)
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->distinct(),

                                TextInput::make('quantity')
                                    ->label('الكمية')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->required(),
                            ])
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->addActionLabel('إضافة مادة خام')
                            ->disabled(
                                fn (string $operation, ?ManufacturingOrder $record) => $operation === 'edit' && $record?->status === ManufacturingOrderStatus::Completed
                            ),
                    ]),
            ])->columns(1);
    }
}
