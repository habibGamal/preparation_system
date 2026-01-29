<?php

declare(strict_types=1);

use App\Enums\SettingKey;
use App\Filament\Pages\Settings;
use App\Models\Setting;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('can render the settings page', function (): void {
    livewire(Settings::class)
        ->assertOk();
});

it('loads default settings values on mount', function (): void {
    // Seed default settings
    Setting::set(SettingKey::MinimumOrdersForRecipe, '3');
    Setting::set(SettingKey::MaximumOrdersForRecipe, '10');
    Setting::set(SettingKey::VarianceWarningThreshold, '10.0');

    livewire(Settings::class)
        ->assertFormSet([
            SettingKey::MinimumOrdersForRecipe->value => '3',
            SettingKey::MaximumOrdersForRecipe->value => '10',
            SettingKey::VarianceWarningThreshold->value => '10.0',
        ]);
});

it('can save settings', function (): void {
    livewire(Settings::class)
        ->fillForm([
            SettingKey::MinimumOrdersForRecipe->value => '5',
            SettingKey::MaximumOrdersForRecipe->value => '20',
            SettingKey::VarianceWarningThreshold->value => '15.5',
            SettingKey::AutoUpdateRecipeOnCompletion->value => 'true',
            SettingKey::RequiredIngredientThreshold->value => '80',
            SettingKey::IncludeIngredientThreshold->value => '40',
        ])
        ->call('save')
        ->assertNotified();

    expect(Setting::get(SettingKey::MinimumOrdersForRecipe))->toBe('5')
        ->and(Setting::get(SettingKey::MaximumOrdersForRecipe))->toBe('20')
        ->and(Setting::get(SettingKey::VarianceWarningThreshold))->toBe('15.5')
        ->and(Setting::get(SettingKey::AutoUpdateRecipeOnCompletion))->toBe('true')
        ->and(Setting::get(SettingKey::RequiredIngredientThreshold))->toBe('80')
        ->and(Setting::get(SettingKey::IncludeIngredientThreshold))->toBe('40');
});

it('validates numeric fields', function (): void {
    livewire(Settings::class)
        ->fillForm([
            SettingKey::MaximumOrdersForRecipe->value => 'invalid',
        ])
        ->call('save')
        ->assertHasFormErrors([SettingKey::MaximumOrdersForRecipe->value]);
});

it('validates required fields', function (): void {
    livewire(Settings::class)
        ->fillForm([
            SettingKey::MinimumOrdersForRecipe->value => '',
        ])
        ->call('save')
        ->assertHasFormErrors([SettingKey::MinimumOrdersForRecipe->value => 'required']);
});

it('can use setting helper function', function (): void {
    Setting::set(SettingKey::MinimumOrdersForRecipe, '5');

    expect(setting(SettingKey::MinimumOrdersForRecipe))->toBe('5');
});

it('returns default value when setting does not exist', function (): void {
    expect(setting('nonexistent_setting', 'default_value'))->toBe('default_value');
});

it('can use setting_int helper', function (): void {
    Setting::set(SettingKey::MaximumOrdersForRecipe, '20');

    expect(setting_int(SettingKey::MaximumOrdersForRecipe))->toBe(20);
});

it('can use setting_float helper', function (): void {
    Setting::set(SettingKey::VarianceWarningThreshold, '10.5');

    expect(setting_float(SettingKey::VarianceWarningThreshold))->toBe(10.5);
});

it('can use setting_bool helper', function (): void {
    Setting::set(SettingKey::AutoUpdateRecipeOnCompletion, 'true');

    expect(setting_bool(SettingKey::AutoUpdateRecipeOnCompletion))->toBeTrue();

    Setting::set(SettingKey::AutoUpdateRecipeOnCompletion, 'false');

    expect(setting_bool(SettingKey::AutoUpdateRecipeOnCompletion))->toBeFalse();
});

it('uses enum default when no setting exists', function (): void {
    // Clear any existing setting
    Setting::where('key', SettingKey::MinimumOrdersForRecipe->value)->delete();

    expect(Setting::get(SettingKey::MinimumOrdersForRecipe))->toBe(SettingKey::MinimumOrdersForRecipe->default());
});
