<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManufacturedProductResource\Pages;

use App\Filament\Resources\ManufacturedProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListManufacturedProducts extends ListRecords
{
    protected static string $resource = ManufacturedProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
