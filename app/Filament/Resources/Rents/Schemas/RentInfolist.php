<?php

namespace App\Filament\Resources\Rents\Schemas;

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
