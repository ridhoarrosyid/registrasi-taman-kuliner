<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                        TextEntry::make('role')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'admin' => 'danger',
                                'tenant' => 'success',
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
