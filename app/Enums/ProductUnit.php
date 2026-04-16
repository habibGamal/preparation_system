<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum ProductUnit: string implements HasLabel
{
    case Kilogram = 'kg';
    case Gram = 'g';
    case Liter = 'l';
    case Milliliter = 'ml';
    case Piece = 'piece';
    case Box = 'box';
    case Package = 'package';

    /**
     * @var array<string, array<int, string>>
     */
    private const IMPORT_ALIASES = [
        'kg' => ['kg', 'كيلو'],
        'g' => ['g', 'جرام'],
        'l' => ['l', 'لتر'],
        'ml' => ['ml', 'زجاجه650', 'زجاجة650'],
        'piece' => ['piece', 'عدد', 'عدد.', 'قطعه', 'قطعة', 'عمود', 'برسيون', 'برطمان', 'جركن', 'زجاجه', 'زجاجة'],
        'box' => ['box', 'علب', 'علبه', 'علبة'],
        'package' => ['package', 'كيس', 'حزمه', 'حزمة', 'باكو', 'باكيت', 'باكت', 'لفة', 'شوال', 'صفيحة'],
    ];

    public static function tryFromImport(mixed $value, bool $closestMatch = true): ?self
    {
        if (blank($value)) {
            return null;
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        $canonicalValue = Str::lower($raw);
        $directMatch = self::tryFrom($canonicalValue);

        if ($directMatch instanceof self) {
            return $directMatch;
        }

        $token = self::normalizeToken($raw);

        foreach (self::IMPORT_ALIASES as $unit => $aliases) {
            foreach ($aliases as $alias) {
                if (self::normalizeToken($alias) === $token) {
                    return self::from($unit);
                }
            }
        }

        if (! $closestMatch) {
            return null;
        }

        $closestUnit = null;
        $closestDistance = PHP_INT_MAX;

        foreach (self::IMPORT_ALIASES as $unit => $aliases) {
            foreach ($aliases as $alias) {
                $distance = levenshtein($token, self::normalizeToken($alias));

                if ($distance < $closestDistance) {
                    $closestDistance = $distance;
                    $closestUnit = $unit;
                }
            }
        }

        return $closestUnit === null ? null : self::from($closestUnit);
    }

    public static function normalizeImportValue(mixed $value, bool $closestMatch = true): ?string
    {
        return self::tryFromImport($value, $closestMatch)?->value;
    }

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

    private static function normalizeToken(string $value): string
    {
        $token = Str::of($value)
            ->trim()
            ->lower()
            ->replace([' ', "\t", "\n", "\r", '_', '-', '.', ',', '،'], '')
            ->toString();

        $token = str_replace(
            ['أ', 'إ', 'آ', 'ؤ', 'ئ', 'ة', 'ى'],
            ['ا', 'ا', 'ا', 'و', 'ي', 'ه', 'ي'],
            $token,
        );

        return (string) preg_replace('/[^\p{L}\p{N}]/u', '', $token);
    }
}
