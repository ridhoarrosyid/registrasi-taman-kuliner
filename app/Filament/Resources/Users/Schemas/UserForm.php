<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255),
                        Select::make('role')
                            ->options([
                                'admin' => 'Administrator',
                                'tenant' => 'Pedagang (Tenant)',
                            ])
                            ->required()
                            ->native(false),
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make('Data Identitas')
                    ->schema([
                        TextInput::make('ktp_number')->label('Nomor KTP')->maxLength(16),
                        TextInput::make('phone_number')->label('Nomor HP')->maxLength(14)->minLength(10)->startsWith('628'),
                        FileUpload::make('ktp_image')->label('Foto KTP')->image()->disk('public')->directory('ktp-images'),
                        ImageEntry::make('ktp_image')->label('Foto KTP Sekarang')->disk('public')->visibility('public')->height(300)->columnSpanFull()
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
