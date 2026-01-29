<?php

declare(strict_types=1);

namespace App\Filament\Resources\RawProductResource\Pages;

use App\Filament\Resources\RawProductResource;
use Filament\Resources\Pages\EditRecord;

final class EditRawProduct extends EditRecord
{
    protected static string $resource = RawProductResource::class;
}
