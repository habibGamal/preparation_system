<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedWasteResource\Pages;

use App\Filament\Resources\ManufacturedWasteResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateManufacturedWaste extends CreateRecord
{
    protected static string $resource = ManufacturedWasteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate total from wasted items
        $total = collect($data['wastedItems'] ?? [])->sum('total');
        $data['total'] = $total;
        unset($data['wastedItems']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
