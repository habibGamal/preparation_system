<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SettingKey;
use App\Models\Setting;
use Illuminate\Database\Seeder;

final class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (SettingKey::cases() as $settingKey) {
            Setting::updateOrCreate(
                ['key' => $settingKey->value],
                ['value' => $settingKey->default()]
            );
        }
    }
}
