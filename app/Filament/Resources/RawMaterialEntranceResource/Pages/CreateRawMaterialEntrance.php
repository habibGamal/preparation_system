<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawMaterialEntranceResource\Pages;

use App\Filament\Resources\RawMaterialEntranceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRawMaterialEntrance extends CreateRecord
{
    protected static string $resource = RawMaterialEntranceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate total from items
        $total = collect($data['items'] ?? [])->sum('total');
        $data['total'] = $total;
        unset($data['items']);

        return $data;
    }
}
