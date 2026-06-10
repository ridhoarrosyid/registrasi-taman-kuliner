<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->description('Detail tagihan dan status penyewaan lapak.')
                    ->schema([
                        TextEntry::make('rent_id')
                            ->label('ID Penyewaan'),

                        TextEntry::make('amount')
                            ->label('Nominal Transaksi')
                            ->money('IDR', locale: 'id'), // Mengubah ke format Rupiah otomatis (Rp 500.000,00)

                        TextEntry::make('status')
                            ->badge() // Membuat status tampil sebagai kotak warna
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('created_at')
                            ->label('Tanggal Pembayaran')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('-'),
                    ])->columns(2), // Membagi informasi ke dalam 2 kolom agar lebih ringkas

                // SECTION 2: Bukti Pembayaran
                Section::make('Dokumen Bukti')
                    ->description('Verifikasi keaslian bukti transfer dari Tenant.')
                    ->schema([
                        ImageEntry::make('payment_proof')
                            ->hiddenLabel() // Label disembunyikan karena judul section sudah jelas
                            ->disk('public')
                            ->visibility('public')
                            ->height(400)
                            ->columnSpanFull(),

                    ]),
            ]);
    }
}
