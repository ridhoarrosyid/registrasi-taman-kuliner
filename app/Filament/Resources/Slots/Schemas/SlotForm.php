<?php

namespace App\Filament\Resources\Slots\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Lapak')
                    ->description('Masukkan kode unik lapak dan status ketersediaannya.')
                    ->schema([
                        TextInput::make('slot_number')
                            ->label('Nomor / Kode Lapak')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Contoh: A1, B2'),

                        Select::make('status')
                            ->label('Status Saat Ini')
                            ->options([
                                'available' => 'Tersedia',
                                'reserved' => 'Dipesan (Pending)',
                                'occupied' => 'Disewa (Aktif)',
                            ])
                            ->required()
                            ->default('available')
                            ->native(false),
                    ])->columns(2)
                    ->columnSpanFull()
            ]);
    }
}
