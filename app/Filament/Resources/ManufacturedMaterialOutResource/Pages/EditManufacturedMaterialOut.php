<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedMaterialOutResource\Pages;

use App\Filament\Components\Actions\CloseManufacturedMaterialOutAction;
use App\Filament\Resources\ManufacturedMaterialOutResource;
use Filament\Resources\Pages\EditRecord;

final class EditManufacturedMaterialOut extends EditRecord
{
    protected static string $resource = ManufacturedMaterialOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CloseManufacturedMaterialOutAction::make(
                redirectUrl: ManufacturedMaterialOutResource::getUrl('index')
            ),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculate total from items
        $total = collect($data['items'] ?? [])->sum('total');
        $data['total'] = $total;
        unset($data['items']);

        return $data;
    }
}
