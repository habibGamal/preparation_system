<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedInventoryResource\Pages;

use App\Filament\Resources\ManufacturedInventoryResource;
use Filament\Resources\Pages\ListRecords;

final class ListManufacturedInventories extends ListRecords
{
    protected static string $resource = ManufacturedInventoryResource::class;
}
