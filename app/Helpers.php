<?php

declare(strict_types=1);

/*
 * Here you can define your own helper functions.
 * Make sure to use the `function_exists` check to not declare the function twice.
 */

if (! function_exists('setting')) {
    /**
     * Get a setting value from the database with optional default fallback.
     */
    function setting(\App\Enums\SettingKey|string $key, mixed $default = null): mixed
    {
        $configKey = $key instanceof \App\Enums\SettingKey ? $key->value : $key;

        return \App\Models\Setting::get($key, $default ?? config('manufacturing.'.$configKey));
    }
}

if (! function_exists('setting_int')) {
    /**
     * Get a setting value as an integer.
     */
    function setting_int(\App\Enums\SettingKey|string $key, int $default = 0): int
    {
        return (int) setting($key, (string) $default);
    }
}

if (! function_exists('setting_float')) {
    /**
     * Get a setting value as a float.
     */
    function setting_float(\App\Enums\SettingKey|string $key, float $default = 0.0): float
    {
        return (float) setting($key, (string) $default);
    }
}

if (! function_exists('setting_bool')) {
    /**
     * Get a setting value as a boolean.
     */
    function setting_bool(\App\Enums\SettingKey|string $key, bool $default = false): bool
    {
        $value = setting($key, $default ? 'true' : 'false');

        return in_array(strtolower((string) $value), ['true', '1', 'yes', 'on'], true);
    }
}

if (! function_exists('example')) {
    function example(): string
    {
        return 'This is an example function you can use in your project.';
    }
}
