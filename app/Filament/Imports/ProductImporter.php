<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\ProductType;
use App\Enums\ProductUnit;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stocktaking;
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
                ->relationship(resolveUsing: fn (mixed $state): ?Category => self::resolveCategory($state))
                // ->rules(['required'])
                ->example('مشتقات الحليب'),
            ImportColumn::make('barcode')
                ->label('باركود')
                ->rules(['max:255'])
                ->example('1234567890123'),
            ImportColumn::make('price')
                ->label('سعر البيع')
                ->requiredMapping()
                ->numeric()
                ->castStateUsing(fn (mixed $state, mixed $originalState): float => self::normalizeDecimal($state, $originalState))
                ->rules([ 'numeric', 'min:0'])
                ->example('25.50'),
            ImportColumn::make('cost')
                ->label('سعر التكلفة')
                ->requiredMapping()
                ->numeric()
                ->castStateUsing(fn (mixed $state, mixed $originalState): float => self::normalizeDecimal($state, $originalState))
                ->rules([ 'numeric', 'min:0'])
                ->example('20.00'),
            ImportColumn::make('min_stock')
                ->label('الحد الأدنى للمخزون')
                ->requiredMapping()
                ->integer()
                ->castStateUsing(fn (mixed $state, mixed $originalState): int => self::normalizeInteger($state, $originalState))
                ->rules([ 'integer', 'min:0'])
                ->example('10'),
            ImportColumn::make('avg_purchase_quantity')
                ->label('متوسط كمية الشراء')
                ->requiredMapping()
                ->integer()
                ->castStateUsing(fn (mixed $state, mixed $originalState): int => self::normalizeInteger($state, $originalState))
                ->rules([ 'integer', 'min:0'])
                ->example('50'),
            ImportColumn::make('quantity')
                ->label('الكمية بالمخزون')
                ->numeric()
                ->castStateUsing(fn (mixed $state, mixed $originalState): float => self::normalizeDecimal($state, $originalState))
                ->fillRecordUsing(function (Product $record, mixed $state): void {
                    // Quantity is processed in afterSave() to create stocktaking rows.
                })
                ->rules(['numeric', 'min:0'])
                ->example('12.5'),
            ImportColumn::make('type')
                ->label('النوع')
                ->requiredMapping()
                ->rules(['required', 'in:raw,manufactured'])
                ->example('raw')
                ->helperText('raw للخام أو manufactured للمصنع'),
            ImportColumn::make('unit')
                ->label('الوحدة')
                ->requiredMapping()
                ->castStateUsing(fn (mixed $state): ?string => ProductUnit::normalizeImportValue($state))
                ->rules(['required', 'in:kg,g,l,ml,piece,box,package'])
                ->example('kg')
                ->helperText('kg, g, l, ml, piece, box, package'),
        ];
    }

    private static function resolveCategory(mixed $state): ?Category
    {
        if (blank($state)) {
            return null;
        }

        if (is_numeric($state)) {
            $category = Category::query()->find((int) $state);

            if ($category instanceof Category) {
                return $category;
            }
        }

        $name = trim((string) $state);

        if ($name === '') {
            return null;
        }

        return Category::query()->firstOrCreate(['name' => $name]);
    }

    private static function normalizeDecimal(mixed $state, mixed $originalState): float
    {
        if (blank($originalState) || blank($state)) {
            return 0.0;
        }

        return max((float) $state, 0.0);
    }

    private static function normalizeInteger(mixed $state, mixed $originalState): int
    {
        if (blank($originalState) || blank($state)) {
            return 0;
        }

        return max((int) $state, 0);
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

    protected function afterSave(): void
    {
        $quantityColumn = $this->columnMap['quantity'] ?? null;

        if (blank($quantityColumn)) {
            return;
        }

        if (! array_key_exists($quantityColumn, $this->originalData)) {
            return;
        }

        if (blank($this->originalData[$quantityColumn])) {
            return;
        }

        $quantity = max((float) ($this->data['quantity'] ?? 0), 0.0);
        $stockQuantity = (float) ($this->record->inventory?->quantity ?? 0);
        $price = (float) ($this->record->price ?? 0);
        $itemTotal = ($quantity - $stockQuantity) * $price;

        $userId = (int) ($this->import->getAttribute('user_id') ?? 0);

        if ($userId <= 0) {
            return;
        }

        $productType = $this->record->type;
        $productTypeValue = $productType instanceof ProductType
            ? $productType->value
            : ProductType::Raw->value;

        $stocktaking = Stocktaking::query()
            ->where('user_id', $userId)
            ->where('product_type', $productTypeValue)
            ->whereNull('closed_at')
            ->oldest('id')
            ->first();

        if (! $stocktaking instanceof Stocktaking) {
            $stocktaking = Stocktaking::query()->create([
                'user_id' => $userId,
                'product_type' => $productTypeValue,
                'total' => 0,
                'notes' => 'تم إنشاؤه تلقائياً عبر استيراد المنتجات #' . $this->import->getKey(),
            ]);
        }

        $stocktaking->items()->create([
            'product_id' => $this->record->getKey(),
            'stock_quantity' => $stockQuantity,
            'real_quantity' => $quantity,
            'price' => $price,
            'total' => $itemTotal,
        ]);

        $stocktaking->update([
            'total' => (float) $stocktaking->total + $itemTotal,
        ]);
    }
}
