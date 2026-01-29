<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\SettingKey;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
final class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|null $navigationLabel = 'الإعدادات';

    protected static string|null $title = 'إعدادات النظام';

    protected string $view = 'filament.pages.settings';

    protected static int|null $navigationSort = 999;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            SettingKey::MinimumOrdersForRecipe->value => Setting::get(SettingKey::MinimumOrdersForRecipe),
            SettingKey::MaximumOrdersForRecipe->value => Setting::get(SettingKey::MaximumOrdersForRecipe),
            SettingKey::VarianceWarningThreshold->value => Setting::get(SettingKey::VarianceWarningThreshold),
            SettingKey::AutoUpdateRecipeOnCompletion->value => Setting::get(SettingKey::AutoUpdateRecipeOnCompletion),
            SettingKey::RequiredIngredientThreshold->value => Setting::get(SettingKey::RequiredIngredientThreshold),
            SettingKey::IncludeIngredientThreshold->value => Setting::get(SettingKey::IncludeIngredientThreshold),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('إعدادات حساب الوصفة')
                        ->description('تكوين كيفية حساب وصفات التصنيع تلقائيًا من أوامر التصنيع السابقة.')
                        ->schema([
                            TextInput::make(SettingKey::MinimumOrdersForRecipe->value)
                                ->label(SettingKey::MinimumOrdersForRecipe->label())
                                ->helperText(SettingKey::MinimumOrdersForRecipe->helperText())
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(100)
                                ->default(SettingKey::MinimumOrdersForRecipe->default()),

                            TextInput::make(SettingKey::MaximumOrdersForRecipe->value)
                                ->label(SettingKey::MaximumOrdersForRecipe->label())
                                ->helperText(SettingKey::MaximumOrdersForRecipe->helperText())
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(1000)
                                ->default(SettingKey::MaximumOrdersForRecipe->default()),

                            TextInput::make(SettingKey::VarianceWarningThreshold->value)
                                ->label(SettingKey::VarianceWarningThreshold->label())
                                ->helperText(SettingKey::VarianceWarningThreshold->helperText())
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.1)
                                ->default(SettingKey::VarianceWarningThreshold->default()),

                            TextInput::make(SettingKey::AutoUpdateRecipeOnCompletion->value)
                                ->label(SettingKey::AutoUpdateRecipeOnCompletion->label())
                                ->helperText(SettingKey::AutoUpdateRecipeOnCompletion->helperText())
                                ->required()
                                ->default(SettingKey::AutoUpdateRecipeOnCompletion->default())
                                ->placeholder('true أو false'),
                        ])
                        ->columns(2),

                    Section::make('عتبات تكرار المكونات')
                        ->description('تكوين كيفية تصنيف المكونات حسب تكرار استخدامها.')
                        ->schema([
                            TextInput::make(SettingKey::RequiredIngredientThreshold->value)
                                ->label(SettingKey::RequiredIngredientThreshold->label())
                                ->helperText(SettingKey::RequiredIngredientThreshold->helperText())
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%')
                                ->default(SettingKey::RequiredIngredientThreshold->default()),

                            TextInput::make(SettingKey::IncludeIngredientThreshold->value)
                                ->label(SettingKey::IncludeIngredientThreshold->label())
                                ->helperText(SettingKey::IncludeIngredientThreshold->helperText())
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%')
                                ->default(SettingKey::IncludeIngredientThreshold->default()),
                        ])
                        ->columns(2),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('حفظ الإعدادات')
                                ->submit('save')
                                ->keyBindings(['mod+s'])
                                ->icon('heroicon-o-check'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (SettingKey::cases() as $settingKey) {
            if (isset($data[$settingKey->value])) {
                Setting::set($settingKey, (string) $data[$settingKey->value]);
            }
        }

        Notification::make()
            ->success()
            ->title('تم الحفظ')
            ->body('تم حفظ الإعدادات بنجاح.')
            ->send();
    }
}
