<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'tenant' => 'success',
                    }),
                TextColumn::make('ktp_number')->label('No. KTP')->placeholder('Belum diisi'),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'tenant' => 'Tenant',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('hubungi_tenant')
                    ->label('Hubungi Tenant')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    // Hanya muncul jika user memiliki nomor HP
                    ->visible(fn($record) => !empty($record?->phone_number))
                    // Buka rute wa.me di tab baru
                    ->url(function ($record) {
                        // Bersihkan nomor HP dari karakter non-angka (spasi, strip, dll)
                        $nomorHp = preg_replace('/[^0-9]/', '', $record->phone_number);

                        // Otomatis ubah angka 0 di depan menjadi 62 jika tenant lupa
                        if (str_starts_with($nomorHp, '0')) {
                            $nomorHp = '62' . substr($nomorHp, 1);
                        }

                        return "https://wa.me/{$nomorHp}";
                    })
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
