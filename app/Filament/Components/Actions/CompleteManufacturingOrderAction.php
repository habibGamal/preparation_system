<?php

declare(strict_types=1);

namespace App\Filament\Components\Actions;

use App\Enums\ManufacturingOrderStatus;
use App\Models\ManufacturingOrder;
use App\Services\ManufacturingOrderService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

final class CompleteManufacturingOrderAction
{
    public static function make(?string $redirectUrl = null): Action
    {
        return Action::make('complete')
            ->label('إكمال التصنيع')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('إكمال إذن التصنيع')
            ->modalDescription(function (ManufacturingOrder $record) {
                $service = app(ManufacturingOrderService::class);
                $warnings = $service->getVarianceWarnings($record);

                $description = 'هل أنت متأكد من إكمال هذا الإذن؟ سيتم خصم المواد الخام من المخزون وإضافة المنتج المصنع.';

                if (count($warnings) > 0) {
                    $description .= "\n\n⚠️ تحذير: يوجد تباين يتجاوز 10% في العناصر التالية:\n";
                    foreach ($warnings as $warning) {
                        $description .= "- {$warning['message']}\n";
                    }
                }

                return $description;
            })
            ->modalSubmitActionLabel('إكمال')
            ->action(function (ManufacturingOrder $record) use ($redirectUrl) {
                app(ManufacturingOrderService::class)->complete($record);

                Notification::make()
                    ->success()
                    ->title('تم إكمال إذن التصنيع')
                    ->body('تم تحديث المخزون بنجاح')
                    ->send();

                if ($redirectUrl !== null) {
                    return redirect()->to($redirectUrl);
                }
            })
            ->visible(fn (ManufacturingOrder $record) => $record->status === ManufacturingOrderStatus::Draft);
    }
}
