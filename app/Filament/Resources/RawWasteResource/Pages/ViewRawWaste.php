<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawWasteResource\Pages;

use App\Filament\Components\Actions\CloseWasteAction;
use App\Filament\Resources\RawWasteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewRawWaste extends ViewRecord
{
    protected static string $resource = RawWasteResource::class;

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
