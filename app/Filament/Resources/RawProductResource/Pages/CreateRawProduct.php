<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawProductResource\Pages;

use App\Filament\Resources\RawProductResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateRawProduct extends CreateRecord
{
    protected static string $resource = RawProductResource::class;
}
