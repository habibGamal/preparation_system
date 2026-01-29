<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedWasteResource\Pages;

use App\Filament\Resources\ManufacturedWasteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListManufacturedWastes extends ListRecords
{
    protected static string $resource = ManufacturedWasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
