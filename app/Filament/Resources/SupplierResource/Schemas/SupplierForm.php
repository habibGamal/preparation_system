<?php

declare(strict_types=1);

namespace App\Filament\Resources\SupplierResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->maxLength(255),
            ]);
    }
}
