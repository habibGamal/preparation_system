<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedMaterialEntranceResource\Pages;

use App\Filament\Resources\ManufacturedMaterialEntranceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListManufacturedMaterialEntrances extends ListRecords
{
    protected static string $resource = ManufacturedMaterialEntranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
