<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawMaterialEntranceResource\Pages;

use App\Filament\Resources\RawMaterialEntranceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListRawMaterialEntrances extends ListRecords
{
    protected static string $resource = RawMaterialEntranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
