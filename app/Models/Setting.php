<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SettingKey;
use Illuminate\Database\Eloquent\Model;

final class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(SettingKey|string $key, mixed $default = null): mixed
    {
        $keyValue = $key instanceof SettingKey ? $key->value : $key;

        $setting = static::where('key', $keyValue)->first();

        if ($setting) {
            return $setting->value;
        }

        // Use enum default if key is enum and no default provided
        if ($key instanceof SettingKey && $default === null) {
            return $key->default();
        }

        return $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(SettingKey|string $key, mixed $value): void
    {
        $keyValue = $key instanceof SettingKey ? $key->value : $key;

        static::updateOrCreate(
            ['key' => $keyValue],
            ['value' => $value]
        );
    }
}
