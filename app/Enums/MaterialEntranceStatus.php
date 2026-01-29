<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MaterialEntranceStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Closed => 'مغلق',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Closed => 'success',
        };
    }
}
