<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedInventoryResource\Pages;

use App\Filament\Resources\ManufacturedInventoryResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewManufacturedInventory extends ViewRecord
{
    protected static string $resource = ManufacturedInventoryResource::class;
}
