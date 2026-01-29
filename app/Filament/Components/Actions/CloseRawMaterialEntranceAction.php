<?php

declare(strict_types=1);

namespace App\Filament\Components\Actions;

use App\Enums\MaterialEntranceStatus;
use App\Models\RawMaterialEntrance;
use App\Services\RawMaterialEntranceService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

final class CloseRawMaterialEntranceAction
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
            ->action(function (RawMaterialEntrance $record) use ($redirectUrl) {
                app(RawMaterialEntranceService::class)->close($record);

                Notification::make()
                    ->success()
                    ->title('تم إغلاق الإدخال')
                    ->body('تم تحديث المخزون بنجاح')
                    ->send();

                if ($redirectUrl !== null) {
                    return redirect()->to($redirectUrl);
                }
            })
            ->visible(fn (RawMaterialEntrance $record) => $record->status === MaterialEntranceStatus::Draft);
    }
}
