<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ManufacturingOrderStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Completed => 'مكتمل',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Completed => 'success',
        };
    }
}
