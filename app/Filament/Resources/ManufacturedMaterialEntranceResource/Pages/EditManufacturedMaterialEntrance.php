<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedMaterialEntranceResource\Pages;

use App\Filament\Components\Actions\CloseManufacturedMaterialEntranceAction;
use App\Filament\Resources\ManufacturedMaterialEntranceResource;
use Filament\Resources\Pages\EditRecord;

final class EditManufacturedMaterialEntrance extends EditRecord
{
    protected static string $resource = ManufacturedMaterialEntranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CloseManufacturedMaterialEntranceAction::make(
                redirectUrl: ManufacturedMaterialEntranceResource::getUrl('index')
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
