<?php

namespace App\Filament\Resources\Rents\Schemas;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Penyewaan')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Pemilik')
                            ->icon('heroicon-m-user'),

                        TextEntry::make('slot.slot_number')
                            ->label('Nomor Lapak')
                            ->icon('heroicon-m-map-pin'),

                        TextEntry::make('business_name')
                            ->label('Nama Usaha')
                            ->icon('heroicon-m-building-storefront'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending_payment', 'payment_failed' => 'gray',
                                'pending_verification', 'renewal_pending_verification' => 'warning',
                                'active' => 'success',
                                'rejected', 'expired' => 'danger',
                            }),
                        Action::make('lihat_bukti')
                            ->label('Lihat Bukti Pembayaran')
                            ->icon('heroicon-o-banknotes')
                            ->color('info') // Warna biru agar kontras
                            ->url(fn($record) => TransactionResource::getUrl('index', [
                                // Ini adalah kunci ajaibnya: Menyuntikkan nilai filter ke URL
                                'filters' => [
                                    'rent_id' => ['value' => $record->id],
                                ],
                            ])),

                    ])->columns(2),

                Section::make('Periode Sewa')
                    ->schema([
                        TextEntry::make('reserved_until')
                            ->label('Batas Reservasi')
                            ->dateTime('d M Y, H:i'),
                        TextEntry::make('start_date')
                            ->label('Mulai')
                            ->date('d M Y'),
                        TextEntry::make('end_date')
                            ->label('Berakhir')
                            ->date('d M Y'),
                    ])->columns(3),
            ]);
    }
}
