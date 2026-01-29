<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawStocktakingResource\Pages;

use App\Filament\Components\Actions\CloseStocktakingAction;
use App\Filament\Resources\RawStocktakingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewRawStocktaking extends ViewRecord
{
    protected static string $resource = RawStocktakingResource::class;

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
