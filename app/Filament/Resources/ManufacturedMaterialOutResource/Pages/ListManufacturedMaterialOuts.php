<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedMaterialOutResource\Pages;

use App\Filament\Resources\ManufacturedMaterialOutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListManufacturedMaterialOuts extends ListRecords
{
    protected static string $resource = ManufacturedMaterialOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
