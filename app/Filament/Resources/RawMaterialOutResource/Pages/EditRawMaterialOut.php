<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawMaterialOutResource\Pages;

use App\Filament\Components\Actions\CloseRawMaterialOutAction;
use App\Filament\Resources\RawMaterialOutResource;
use Filament\Resources\Pages\EditRecord;

final class EditRawMaterialOut extends EditRecord
{
    protected static string $resource = RawMaterialOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CloseRawMaterialOutAction::make(
                redirectUrl: RawMaterialOutResource::getUrl('index')
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
