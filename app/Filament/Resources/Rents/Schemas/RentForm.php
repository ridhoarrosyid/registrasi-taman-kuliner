<?php

namespace App\Filament\Resources\Rents\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('slot_id')
                    ->required()
                    ->numeric(),
                TextInput::make('business_name')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('pending_payment'),
                DateTimePicker::make('reserved_until')
                    ->required(),
                DatePicker::make('start_date'),
                DatePicker::make('end_date'),
                TextInput::make('qr_code'),
            ]);
    }
}
