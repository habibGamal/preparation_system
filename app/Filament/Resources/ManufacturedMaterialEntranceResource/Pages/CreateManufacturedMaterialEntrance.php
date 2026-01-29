<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedMaterialEntranceResource\Pages;

use App\Filament\Resources\ManufacturedMaterialEntranceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateManufacturedMaterialEntrance extends CreateRecord
{
    protected static string $resource = ManufacturedMaterialEntranceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate total from items
        $total = collect($data['items'] ?? [])->sum('total');
        $data['total'] = $total;
        unset($data['items']);

        return $data;
    }
}
