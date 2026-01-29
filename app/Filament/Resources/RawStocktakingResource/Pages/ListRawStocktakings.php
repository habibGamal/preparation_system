<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawStocktakingResource\Pages;

use App\Filament\Resources\RawStocktakingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListRawStocktakings extends ListRecords
{
    protected static string $resource = RawStocktakingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
