<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawInventoryResource\Pages;

use App\Filament\Resources\RawInventoryResource;
use Filament\Resources\Pages\ListRecords;

final class ListRawInventories extends ListRecords
{
    protected static string $resource = RawInventoryResource::class;
}
