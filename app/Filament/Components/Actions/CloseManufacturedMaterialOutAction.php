<?php

declare(strict_types=1);

namespace App\Filament\Components\Actions;

use App\Enums\MaterialEntranceStatus;
use App\Models\ManufacturedMaterialOut;
use App\Services\ManufacturedMaterialOutService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

final class CloseManufacturedMaterialOutAction
{
    public static function make(?string $redirectUrl = null): Action
    {
        return Action::make('close')
            ->label('إغلاق الإخراج')
            ->icon('heroicon-o-lock-closed')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('إغلاق الإخراج')
            ->modalDescription('هل أنت متأكد من إغلاق هذا الإخراج؟ سيتم تحديث المخزون ولن يمكن التعديل بعد ذلك.')
            ->modalSubmitActionLabel('إغلاق')
            ->action(function (ManufacturedMaterialOut $record) use ($redirectUrl) {
                app(ManufacturedMaterialOutService::class)->close($record);

                Notification::make()
                    ->success()
                    ->title('تم إغلاق الإخراج')
                    ->body('تم تحديث المخزون بنجاح')
                    ->send();

                if ($redirectUrl !== null) {
                    return redirect()->to($redirectUrl);
                }
            })
            ->visible(fn (ManufacturedMaterialOut $record) => $record->status === MaterialEntranceStatus::Draft);
    }
}
