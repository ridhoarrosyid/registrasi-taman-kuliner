<?php

namespace App\Filament\Resources\Rents\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pemilik')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slot.slotGroup.name')
                    ->label('Blok Tenant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slot.slot_number')
                    ->label('Lapak')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('business_name')
                    ->label('Nama Usaha')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending_payment', 'payment_failed' => 'gray',
                        'pending_verification', 'renewal_pending_verification' => 'warning',
                        'active' => 'success',
                        'rejected', 'expired' => 'danger',
                    }),

                TextColumn::make('end_date')
                    ->label('Berakhir Pada')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('cetak_placard')
                    ->label('Cetak Label')
                    ->icon('heroicon-o-qr-code')
                    ->color('warning') // Warna kuning oranye agar menarik perhatian
                    // Hanya muncul jika status sewa sudah disetujui (aktif)
                    ->visible(fn($record) => $record->status === 'active')
                    ->action(function ($record) {

                        // 1. Tentukan URL tujuan scan QR (ganti rute verifikasi sesuai sistem Anda)
                        // Misal dialihkan ke halaman publik cek status lapak
                        $urlVerifikasi = url("/verifikasi/lapak/{$record->id}");

                        // 2. Generate QR Code ke dalam format string Base64 PNG
                        $qrCodeBase64 = base64_encode(
                            QrCode::format('svg')
                                ->size(200)
                                ->margin(1)
                                ->generate($urlVerifikasi)
                        );

                        // 3. Masukkan data ke dalam view blade dan konversi ke PDF
                        $pdf = Pdf::loadView('pdf.placeCard', [
                            'rent' => $record,
                            'qrCode' => $qrCodeBase64,
                        ])->setPaper('a4', 'landscape');

                        // 4. Perintahkan browser untuk langsung mengunduh file hasil generate
                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            'Label_Lapak_' . $record->slot->slot_number . '.pdf'
                        );
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->label('Eksport ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename('Laporan_Penyewaan_BPU_' . date('Y-m-d'))
                        ]),
                ]),
            ]);
    }
}
