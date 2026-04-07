<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\ManufacturedMaterialEntranceItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

final class ManufacturedSupplierEntranceReportExporter extends Exporter
{
    protected static ?string $model = ManufacturedMaterialEntranceItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('product_name')
                ->label('المنتج'),
            ExportColumn::make('total_quantity')
                ->label('إجمالي الكمية')
                ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2)),
            ExportColumn::make('average_price')
                ->label('متوسط السعر')
                ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2)),
            ExportColumn::make('total_value')
                ->label('القيمة الإجمالية')
                ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2))
                ->suffix(' ج.م'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير ' . Number::format($export->successful_rows) . ' سجل بنجاح.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' فشل تصدير ' . Number::format($failedRowsCount) . ' سجل.';
        }

        return $body;
    }
}
