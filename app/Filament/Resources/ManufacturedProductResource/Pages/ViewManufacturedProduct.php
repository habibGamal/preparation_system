<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedProductResource\Pages;

use App\Filament\Resources\ManufacturedProductResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewManufacturedProduct extends ViewRecord
{
    protected static string $resource = ManufacturedProductResource::class;
}
