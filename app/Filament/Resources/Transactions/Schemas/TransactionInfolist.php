<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Penyewa')
                    ->schema([
                        TextEntry::make('rent.user.name')->label('Nama Lengkap')->icon('heroicon-m-user'),
                        TextEntry::make('rent.user.email')->label('Alamat Email')->icon('heroicon-m-envelope'),
                        TextEntry::make('rent.user.phone_number')->label('Whatsapp')->icon(Heroicon::Phone),
                        TextEntry::make('rent.user.ktp_number')->label('NIK')->copyable()->icon('heroicon-m-identification'),
                        ImageEntry::make('rent.user.ktp_image')->label('Foto KTP')->disk('public')->visibility('public')->height(300)->columnSpanFull(),
                        Action::make('rent.user.phone_number')
                            ->label('Chat WA')
                            ->icon(Heroicon::ChatBubbleBottomCenter)
                            ->color('success') // Warna hijau agar kontras
                            ->url(function ($record) {
                                // 1. Ambil nomor HP dari tabel user
                                $nomorHp = $record->rent->user->phone_number;

                                // 2. Bersihkan semua karakter selain angka (misal ada spasi, strip, atau tanda +)
                                $nomorHp = preg_replace('/[^0-9]/', '', $nomorHp);

                                // 3. Ubah angka 0 di depan menjadi 62 (Standar kode negara Indonesia untuk wa.me)
                                if (str_starts_with($nomorHp, '0')) {
                                    $nomorHp = '62' . substr($nomorHp, 1);
                                }

                                // 4. (Opsional) Buat template pesan otomatis yang menyapa nama penyewa
                                $namaPenyewa = $record->rent->user->name ?? 'Bapak/Ibu';
                                $namaBisnis = $record->rent->business_name ?? 'Tenant';
                                $pesan = urlencode("Halo {$namaPenyewa} pengelola booth {$namaBisnis}, saya Admin BPU Universitas Lampung ingin menginformasikan terkait penyewaan lapak Anda...");

                                // 5. Kembalikan URL lengkapnya
                                return "https://wa.me/{$nomorHp}?text={$pesan}";
                            }),

                    ])->columns(2),
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
                                'success' => 'success',
                                'failed' => 'danger',
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
                            ->columnSpanFull()
                            ->extraImgAttributes([
                                'class' => 'w-full rounded-xl border border-gray-200',
                                'style' => 'width: 100%; max-width: 100%; height: auto;',
                            ]),
                    ]),
            ]);
    }
}
