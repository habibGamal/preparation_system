<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawWasteResource\Pages;

use App\Filament\Resources\RawWasteResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRawWaste extends CreateRecord
{
    protected static string $resource = RawWasteResource::class;

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
