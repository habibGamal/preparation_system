<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturingOrderResource\Pages;

use App\Filament\Components\Actions\CompleteManufacturingOrderAction;
use App\Filament\Resources\ManufacturingOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditManufacturingOrder extends EditRecord
{
    protected static string $resource = ManufacturingOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CompleteManufacturingOrderAction::make(
                redirectUrl: ManufacturingOrderResource::getUrl('index')
            ),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['items']);

        return $data;
    }
}
