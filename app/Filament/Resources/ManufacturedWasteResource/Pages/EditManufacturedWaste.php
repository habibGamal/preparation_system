<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedWasteResource\Pages;

use App\Filament\Components\Actions\CloseWasteAction;
use App\Filament\Resources\ManufacturedWasteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditManufacturedWaste extends EditRecord
{
    protected static string $resource = ManufacturedWasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->getRecord()->canBeDeleted()),
            CloseWasteAction::make($this->getResource()::getUrl('view', ['record' => $this->getRecord()])),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculate total from wasted items
        if (isset($data['wastedItems']) && is_array($data['wastedItems'])) {
            $total = collect($data['wastedItems'])->sum('total');
            $data['total'] = $total;
            unset($data['wastedItems']);
        }

        return $data;
    }
}
