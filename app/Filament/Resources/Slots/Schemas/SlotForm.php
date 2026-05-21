<?php

namespace App\Filament\Resources\Slots\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slot_number')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('available'),
            ]);
    }
}
