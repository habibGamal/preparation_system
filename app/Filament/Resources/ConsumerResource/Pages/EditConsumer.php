<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConsumerResource\Pages;

use App\Filament\Resources\ConsumerResource;
use Filament\Resources\Pages\EditRecord;

final class EditConsumer extends EditRecord
{
    protected static string $resource = ConsumerResource::class;
}
