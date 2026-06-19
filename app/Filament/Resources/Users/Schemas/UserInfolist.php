<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Utama')
                    ->schema([
                        TextEntry::make('name')->label('Nama Lengkap')->icon('heroicon-m-user'),
                        TextEntry::make('email')->label('Alamat Email')->icon('heroicon-m-envelope'),
                        TextEntry::make('phone_number')->label('Whatsapp')->icon(Heroicon::Phone),
                        TextEntry::make('role')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'admin' => 'danger',
                                'tenant' => 'success',
                            }),
                        Action::make('phone_number')
                            ->label('Chat WA')
                            ->icon(Heroicon::ChatBubbleBottomCenter)
                            ->color('success') // Warna hijau agar kontras
                            ->url(function ($record) {
                                // 1. Ambil nomor HP dari tabel user
                                $nomorHp = $record->phone_number;

                                // 2. Bersihkan semua karakter selain angka (misal ada spasi, strip, atau tanda +)
                                $nomorHp = preg_replace('/[^0-9]/', '', $nomorHp);

                                // 3. Ubah angka 0 di depan menjadi 62 (Standar kode negara Indonesia untuk wa.me)
                                if (str_starts_with($nomorHp, '0')) {
                                    $nomorHp = '62' . substr($nomorHp, 1);
                                }

                                // 4. (Opsional) Buat template pesan otomatis yang menyapa nama penyewa
                                $namaPenyewa = $record->name ?? 'Bapak/Ibu';
                                $pesan = urlencode("Halo {$namaPenyewa}, saya Admin BPU Universitas Lampung ingin menginformasikan terkait penyewaan lapak Anda...");

                                // 5. Kembalikan URL lengkapnya
                                return "https://wa.me/{$nomorHp}?text={$pesan}";
                            }),

                    ])->columns(2),

                Section::make('Verifikasi Identitas Resmi')
                    ->schema([
                        TextEntry::make('ktp_number')->label('NIK')->copyable()->icon('heroicon-m-identification'),
                        ImageEntry::make('ktp_image')->label('Foto KTP')->disk('public')->visibility('public')->height(300)->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
