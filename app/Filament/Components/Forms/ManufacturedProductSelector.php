<?php

declare(strict_types=1);

namespace App\Filament\Components\Forms;

use App\Enums\ProductType;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

final class ManufacturedProductSelector extends Select
{
    /**
     * @var callable|null
     */
    protected $additionalPropsCallback;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('إضافة منتج')
            ->placeholder('اختر منتج لإضافته...')
            ->searchable()
            ->allowHtml()
            ->options(function (): array {
                $products = Product::query()
                    ->where('type', ProductType::Manufactured)
                    ->with('category')
                    ->get();

                return $products->mapWithKeys(function (Product $product): array {
                    $price = $product->cost ?? $product->price;
                    $categoryName = $product->category?->name ?? 'بدون فئة';

                    $label = $product->name.' - '.$price.' ج.م'.' ('.$categoryName.')';

                    return [$product->id => $label];
                })->toArray();
            })
            ->live()
            ->afterStateUpdated(function (mixed $state, Set $set, Get $get): void {
                if (! $state) {
                    return;
                }

                /** @phpstan-ignore-next-line */
                $product = Product::query()->find($state);
                if (! $product) {
                    return;
                }

                $currentItems = $get('items') ?? [];
                // Check if product already exists in the list
                $existingProductIds = collect($currentItems)->pluck('product_id')->filter()->toArray();

                if (in_array($product->id, $existingProductIds, true)) {
                    Notification::make()
                        ->title('تحذير')
                        ->body('هذا المنتج موجود بالفعل في القائمة')
                        ->warning()
                        ->send();

                    // Reset the select
                    $set('product_selector', null);

                    return;
                }

                // Prepare new item data
                $price = $product->cost ?? $product->price;
                $quantity = 1;
                $total = $quantity * $price;

                $newItem = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total,
                ];

                // Add additional props if callback is provided
                if ($this->additionalPropsCallback !== null) {
                    $additionalProps = ($this->additionalPropsCallback)($product);
                    $newItem = array_merge($newItem, $additionalProps);
                }

                // Add the new item
                $currentItems[] = $newItem;
                $set('items', $currentItems);

                // Recalculate total
                $invoiceTotal = 0;
                foreach ($currentItems as $item) {
                    $invoiceTotal += $item['total'] ?? 0;
                }
                $set('total', $invoiceTotal);

                // Reset the select
                $set('product_selector', null);
            })
            ->dehydrated(false); // Don't save this field's value
    }

    public static function make(?string $name = 'product_selector'): static
    {
        return parent::make($name);
    }

    public function additionalProps(callable $additional): static
    {
        $this->additionalPropsCallback = $additional;

        return $this;
    }
}
