<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedStocktakingResource\Pages;

use App\Filament\Components\Actions\CloseStocktakingAction;
use App\Filament\Resources\ManufacturedStocktakingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditManufacturedStocktaking extends EditRecord
{
    protected static string $resource = ManufacturedStocktakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->getRecord()->canBeDeleted()),
            CloseStocktakingAction::make(
                $this->getResource()::getUrl('view', ['record' => $this->getRecord()])
            ),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculate total from items
        if (isset($data['items']) && is_array($data['items'])) {
            $total = collect($data['items'])->sum('total');
            $data['total'] = $total;
            unset($data['items']);
        }

        return $data;
    }
}
