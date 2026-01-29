<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedStocktakingResource\Pages;

use App\Filament\Components\Actions\CloseStocktakingAction;
use App\Filament\Resources\ManufacturedStocktakingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewManufacturedStocktaking extends ViewRecord
{
    protected static string $resource = ManufacturedStocktakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->getRecord()->canBeEdited()),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->getRecord()->canBeDeleted()),
            CloseStocktakingAction::make(),
        ];
    }
}
