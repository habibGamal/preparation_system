<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawMaterialEntranceResource\Pages;

use App\Filament\Components\Actions\CloseRawMaterialEntranceAction;
use App\Filament\Resources\RawMaterialEntranceResource;
use Filament\Resources\Pages\EditRecord;

final class EditRawMaterialEntrance extends EditRecord
{
    protected static string $resource = RawMaterialEntranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CloseRawMaterialEntranceAction::make(
                redirectUrl: RawMaterialEntranceResource::getUrl('index')
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
