<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

final class ManufacturedMaterialReportExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('المنتج'),
            ExportColumn::make('start_quantity')
                ->label('رصيد البداية')
                ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2)),
            ExportColumn::make('inlet_quantity')
                ->label('الإدخال')
                ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2)),
            ExportColumn::make('outlet_quantity')
                ->label('الإخراج')
                ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2)),
            ExportColumn::make('stocktaking_quantity')
                ->label('تسوية الجرد')
                ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2)),
            ExportColumn::make('waste_quantity')
                ->label('التالف')
                ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2)),
            ExportColumn::make('end_quantity')
                ->label('رصيد النهاية')
                ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2)),
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
