<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConsumerResource\Pages;

use App\Filament\Resources\ConsumerResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateConsumer extends CreateRecord
{
    protected static string $resource = ConsumerResource::class;
}
