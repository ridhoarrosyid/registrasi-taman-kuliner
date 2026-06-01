<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('rent_id')
                    ->numeric(),
                TextEntry::make('amount')
                    ->numeric(),
                ImageEntry::make('payment_proof')
                    ->label('Bukti Pembayaran')
                    ->disk('public') // Pastikan disk diarahkan ke public (storage/app/public)
                    ->visibility('public')
                    ->height(400) // Buat ukurannya cukup besar agar admin mudah memverifikasi angka di struk
                    ->columnSpanFull(),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
