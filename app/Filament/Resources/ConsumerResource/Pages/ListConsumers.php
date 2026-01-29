<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConsumerResource\Pages;

use App\Filament\Resources\ConsumerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListConsumers extends ListRecords
{
    protected static string $resource = ConsumerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
