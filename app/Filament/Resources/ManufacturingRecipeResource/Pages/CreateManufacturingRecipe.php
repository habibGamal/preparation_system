<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturingRecipeResource\Pages;

use App\Filament\Resources\ManufacturingRecipeResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateManufacturingRecipe extends CreateRecord
{
    protected static string $resource = ManufacturingRecipeResource::class;
}
