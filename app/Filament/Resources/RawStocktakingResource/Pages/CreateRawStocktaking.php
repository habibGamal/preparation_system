<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawStocktakingResource\Pages;

use App\Enums\ProductType;
use App\Filament\Resources\RawStocktakingResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRawStocktaking extends CreateRecord
{
    protected static string $resource = RawStocktakingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate total from items
        $total = collect($data['items'] ?? [])->sum('total');
        $data['total'] = $total;
        $data['product_type'] = ProductType::Raw->value;
        unset($data['items']);
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
