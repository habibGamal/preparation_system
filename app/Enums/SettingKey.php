<?php

declare(strict_types=1);

namespace App\Enums;

enum SettingKey: string
{
    case MinimumOrdersForRecipe = 'minimum_orders_for_recipe';
    case MaximumOrdersForRecipe = 'maximum_orders_for_recipe';
    case VarianceWarningThreshold = 'variance_warning_threshold';
    case AutoUpdateRecipeOnCompletion = 'auto_update_recipe_on_completion';
    case RequiredIngredientThreshold = 'required_ingredient_threshold';
    case IncludeIngredientThreshold = 'include_ingredient_threshold';

    /**
     * Get the default value for this setting.
     */
    public function default(): string
    {
        return match ($this) {
            self::MinimumOrdersForRecipe => '3',
            self::MaximumOrdersForRecipe => '10',
            self::VarianceWarningThreshold => '10.0',
            self::AutoUpdateRecipeOnCompletion => 'true',
            self::RequiredIngredientThreshold => '70',
            self::IncludeIngredientThreshold => '30',
        };
    }

    /**
     * Get the Arabic label for this setting.
     */
    public function label(): string
    {
        return match ($this) {
            self::MinimumOrdersForRecipe => 'الحد الأدنى من الأوامر للوصفة',
            self::MaximumOrdersForRecipe => 'الحد الأقصى من الأوامر للوصفة',
            self::VarianceWarningThreshold => 'عتبة تحذير التباين (%)',
            self::AutoUpdateRecipeOnCompletion => 'تحديث الوصفة تلقائيًا',
            self::RequiredIngredientThreshold => 'عتبة المكون المطلوب (%)',
            self::IncludeIngredientThreshold => 'عتبة تضمين المكون (%)',
        };
    }

    /**
     * Get the helper text for this setting.
     */
    public function helperText(): string
    {
        return match ($this) {
            self::MinimumOrdersForRecipe => 'الحد الأدنى من الأوامر المكتملة المطلوبة لإنشاء وصفة موثوقة',
            self::MaximumOrdersForRecipe => 'الحد الأقصى من الأوامر المستخدمة في حساب الوصفة (بعد بلوغه، لن يتم إعادة الحساب)',
            self::VarianceWarningThreshold => 'نسبة الانحراف عن الوصفة لإظهار التحذير',
            self::AutoUpdateRecipeOnCompletion => 'تحديث الوصفة تلقائيًا بعد كل إكمال طلب (true/false)',
            self::RequiredIngredientThreshold => 'الحد الأدنى من التكرار لاعتبار المكون مطلوبًا (تحذير في حالة الفقدان)',
            self::IncludeIngredientThreshold => 'الحد الأدنى من التكرار لتضمين المكون في الوصفة (أقل = يتم تجاهله)',
        };
    }
}
