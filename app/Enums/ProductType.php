<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductType: string implements HasColor, HasLabel
{
    case Raw = 'raw';
    case Manufactured = 'manufactured';

    public function getLabel(): string
    {
        return match ($this) {
            self::Raw => 'خام',
            self::Manufactured => 'مصنع',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Raw => 'info',
            self::Manufactured => 'warning',
        };
    }
}
