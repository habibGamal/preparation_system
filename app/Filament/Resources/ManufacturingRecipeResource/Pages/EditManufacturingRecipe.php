<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturingRecipeResource\Pages;

use App\Filament\Resources\ManufacturingRecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditManufacturingRecipe extends EditRecord
{
    protected static string $resource = ManufacturingRecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
