<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedWasteResource\Pages;

use App\Filament\Components\Actions\CloseWasteAction;
use App\Filament\Resources\ManufacturedWasteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewManufacturedWaste extends ViewRecord
{
    protected static string $resource = ManufacturedWasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->getRecord()->canBeEdited()),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->getRecord()->canBeDeleted()),
            CloseWasteAction::make(),
        ];
    }
}
