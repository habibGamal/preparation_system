<?php

declare(strict_types=1);

namespace App\Filament\Components\Actions;

use App\Enums\MaterialEntranceStatus;
use App\Models\ManufacturedMaterialEntrance;
use App\Services\ManufacturedMaterialEntranceService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

final class CloseManufacturedMaterialEntranceAction
{
    public static function make(?string $redirectUrl = null): Action
    {
        return Action::make('close')
            ->label('إغلاق الإدخال')
            ->icon('heroicon-o-lock-closed')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('إغلاق الإدخال')
            ->modalDescription('هل أنت متأكد من إغلاق هذا الإدخال؟ سيتم تحديث المخزون ولن يمكن التعديل بعد ذلك.')
            ->modalSubmitActionLabel('إغلاق')
            ->action(function (ManufacturedMaterialEntrance $record) use ($redirectUrl) {
                app(ManufacturedMaterialEntranceService::class)->close($record);

                Notification::make()
                    ->success()
                    ->title('تم إغلاق الإدخال')
                    ->body('تم تحديث المخزون بنجاح')
                    ->send();

                if ($redirectUrl !== null) {
                    return redirect()->to($redirectUrl);
                }
            })
            ->visible(fn (ManufacturedMaterialEntrance $record) => $record->status === MaterialEntranceStatus::Draft);
    }
}
