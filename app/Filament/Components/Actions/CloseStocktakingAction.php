<?php

declare(strict_types=1);

namespace App\Filament\Components\Actions;

use App\Models\Stocktaking;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

final class CloseStocktakingAction
{
    public static function make(?string $redirectUrl = null): Action
    {
        return Action::make('close')
            ->label('إغلاق الجرد')
            ->icon('heroicon-o-lock-closed')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('إغلاق الجرد')
            ->modalDescription('هل أنت متأكد من إغلاق الجرد؟ بعد الإغلاق سيتم تحديث المخزون ولن يمكن التعديل أو الحذف.')
            ->modalSubmitActionLabel('نعم، إغلاق الجرد')
            ->action(function (Stocktaking $record) use ($redirectUrl) {
                DB::transaction(function () use ($record): void {
                    $record->closed_at = now();
                    $record->save();
                });

                Notification::make()
                    ->success()
                    ->title('تم إغلاق الجرد بنجاح')
                    ->body('تم تحديث المخزون بناءً على نتائج الجرد')
                    ->send();

                if ($redirectUrl !== null) {
                    return redirect()->to($redirectUrl);
                }
            })
            ->visible(fn (Stocktaking $record) => ! $record->isClosed());
    }
}
