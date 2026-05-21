<?php

namespace App\Filament\Resources\Rents\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penyewa & Lapak')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name') // Mengambil nama dari tabel users
                            ->label('Nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('slot_id')
                            ->relationship('slot', 'slot_number') // Mengambil nomor dari tabel slots
                            ->label('Nomor Lapak')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('business_name')
                            ->label('Nama Bisnis')
                            ->required()
                            ->maxLength(255),

                        Select::make('status')
                            ->label('Status Sewa')
                            ->options([
                                'pending_payment' => 'Menunggu Pembayaran',
                                'payment_failed' => 'Pembayaran Gagal',
                                'pending_verification' => 'Menunggu Verifikasi',
                                'renewal_pending_verification' => 'Perpanjangan - Menunggu Verifikasi',
                                'active' => 'Aktif (Disewa)',
                                'rejected' => 'Ditolak',
                                'expired' => 'Kedaluwarsa',
                            ])
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Durasi & Waktu')
                    ->schema([
                        DateTimePicker::make('reserved_until')
                            ->label('Batas Waktu Reservasi')
                            ->required(),

                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai Sewa'),

                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai Sewa'),
                    ]),
            ]);
    }
}
