<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rent.user.name')
                    ->label('Nama Tenant')
                    ->searchable(),

                TextColumn::make('rent.slot.slotGroup.name')
                    ->label('Lapak')
                    ->badge()
                    ->color('info'),

                TextColumn::make('rent.slot.slot_number')
                    ->label('Lapak')
                    ->badge()
                    ->color('info'),

                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                ImageColumn::make('payment_proof')
                    ->disk('public')
                    ->label('Bukti Transfer'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('rent_id')
                    ->relationship('rent', 'business_name')
                    ->label('Berdasarkan Penyewaan')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->label('Unduh Laporan Excel')
                        ->icon('heroicon-o-document-chart-bar')
                        ->color('success')
                        ->exports([
                            ExcelExport::make()
                                // Mengatur nama file unduhan otomatis berdasarkan tanggal hari ini
                                ->withFilename('Laporan_Transaksi_BPU_' . date('Y-m-d'))
                                // Menentukan susunan kolom di dalam file Excel beserta judul header-nya
                                ->withColumns([
                                    Column::make('id')->heading('ID Transaksi'),
                                    Column::make('rent.business_name')->heading('Nama Bisnis / Tenant'),
                                    Column::make('rent.slot.slot_number')->heading('Nomor Lapak'),

                                    // PERUBAHAN DI SINI: Mengubah jalur teks menjadi Formula Link Excel
                                    Column::make('payment_proof')
                                        ->heading('Link Bukti Transfer')
                                        ->getStateUsing(
                                            fn($record) => $record->payment_proof
                                                ? Storage::disk('public')->url($record->payment_proof)
                                                : '-'
                                        ),

                                    Column::make('amount')
                                        ->heading('Nominal Transfer')
                                        ->format('#,##0'),
                                    Column::make('status')->heading('Status Verifikasi'),
                                    Column::make('created_at')->heading('Tanggal Upload'),
                                ]),
                        ]),
                ]),
            ]);
    }
}
