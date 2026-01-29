<?php

declare(strict_types=1);

namespace App\Filament\Components\Actions;

use App\Models\Waste;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

final class CloseWasteAction
{
    public static function make(?string $redirectUrl = null): Action
    {
        return Action::make('close')
            ->label('إغلاق التالف')
            ->icon('heroicon-o-lock-closed')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('إغلاق التالف')
            ->modalDescription('هل أنت متأكد من إغلاق التالف؟ بعد الإغلاق سيتم خصم الكميات من المخزون ولن يمكن التعديل أو الحذف.')
            ->modalSubmitActionLabel('نعم، إغلاق التالف')
            ->action(function (Waste $record) use ($redirectUrl) {
                DB::transaction(function () use ($record): void {
                    $record->closed_at = now();
                    $record->save();
                });

                Notification::make()
                    ->success()
                    ->title('تم إغلاق التالف بنجاح')
                    ->body('تم خصم الكميات من المخزون')
                    ->send();

                if ($redirectUrl !== null) {
                    return redirect()->to($redirectUrl);
                }
            })
            ->visible(fn (Waste $record) => ! $record->isClosed());
    }
}
