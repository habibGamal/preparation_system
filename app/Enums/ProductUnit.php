<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductUnit: string implements HasLabel
{
    case Kilogram = 'kg';
    case Gram = 'g';
    case Liter = 'l';
    case Milliliter = 'ml';
    case Piece = 'piece';
    case Box = 'box';
    case Package = 'package';

    public function getLabel(): string
    {
        return match ($this) {
            self::Kilogram => 'كيلوجرام',
            self::Gram => 'جرام',
            self::Liter => 'لتر',
            self::Milliliter => 'ملليلتر',
            self::Piece => 'قطعة',
            self::Box => 'صندوق',
            self::Package => 'عبوة',
        };
    }
}
