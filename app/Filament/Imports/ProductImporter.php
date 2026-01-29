<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

final class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('الاسم')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('حليب بارد'),
            ImportColumn::make('category')
                ->label('الفئة')
                ->requiredMapping()
                ->relationship()
                ->rules(['required'])
                ->example('مشتقات الحليب'),
            ImportColumn::make('barcode')
                ->label('باركود')
                ->rules(['max:255'])
                ->example('1234567890123'),
            ImportColumn::make('price')
                ->label('سعر البيع')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0'])
                ->example('25.50'),
            ImportColumn::make('cost')
                ->label('سعر التكلفة')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0'])
                ->example('20.00'),
            ImportColumn::make('min_stock')
                ->label('الحد الأدنى للمخزون')
                ->requiredMapping()
                ->integer()
                ->rules(['required', 'integer', 'min:0'])
                ->example('10'),
            ImportColumn::make('avg_purchase_quantity')
                ->label('متوسط كمية الشراء')
                ->requiredMapping()
                ->integer()
                ->rules(['required', 'integer', 'min:0'])
                ->example('50'),
            ImportColumn::make('type')
                ->label('النوع')
                ->requiredMapping()
                ->rules(['required', 'in:raw,manufactured'])
                ->example('raw')
                ->helperText('raw للخام أو manufactured للمصنع'),
            ImportColumn::make('unit')
                ->label('الوحدة')
                ->requiredMapping()
                ->rules(['required', 'in:kg,g,l,ml,piece,box,package'])
                ->example('kg')
                ->helperText('kg, g, l, ml, piece, box, package'),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'تم استيراد '.Number::format($import->successful_rows).' سجل بنجاح.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' فشل استيراد '.Number::format($failedRowsCount).' سجل.';
        }

        return $body;
    }

    public function resolveRecord(): Product
    {
        return new Product();
    }
}
