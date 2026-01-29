<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

final class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label('الاسم'),
            ExportColumn::make('category.name')
                ->label('الفئة'),
            ExportColumn::make('barcode')
                ->label('باركود'),
            ExportColumn::make('price')
                ->label('سعر البيع')
                ->suffix(' ج.م'),
            ExportColumn::make('cost')
                ->label('سعر التكلفة')
                ->suffix(' ج.م'),
            ExportColumn::make('min_stock')
                ->label('الحد الأدنى للمخزون'),
            ExportColumn::make('avg_purchase_quantity')
                ->label('متوسط كمية الشراء'),
            ExportColumn::make('type')
                ->label('النوع')
                ->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('unit')
                ->label('الوحدة')
                ->formatStateUsing(fn ($state) => $state?->getLabel()),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
            ExportColumn::make('updated_at')
                ->label('تاريخ التحديث'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير '.Number::format($export->successful_rows).' سجل بنجاح.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' فشل تصدير '.Number::format($failedRowsCount).' سجل.';
        }

        return $body;
    }
}
