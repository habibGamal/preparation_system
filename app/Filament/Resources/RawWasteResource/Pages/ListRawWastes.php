<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawWasteResource\Pages;

use App\Filament\Resources\RawWasteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListRawWastes extends ListRecords
{
    protected static string $resource = RawWasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
