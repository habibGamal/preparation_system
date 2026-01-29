<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedStocktakingResource\Pages;

use App\Filament\Resources\ManufacturedStocktakingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListManufacturedStocktakings extends ListRecords
{
    protected static string $resource = ManufacturedStocktakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
