<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawProductResource\Pages;

use App\Filament\Resources\RawProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListRawProducts extends ListRecords
{
    protected static string $resource = RawProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
