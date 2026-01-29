<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawMaterialOutResource\Pages;

use App\Filament\Resources\RawMaterialOutResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRawMaterialOut extends CreateRecord
{
    protected static string $resource = RawMaterialOutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate total from items
        $total = collect($data['items'] ?? [])->sum('total');
        $data['total'] = $total;
        unset($data['items']);

        return $data;
    }
}
