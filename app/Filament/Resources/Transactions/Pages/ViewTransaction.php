<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Validasi Pembayaran')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembayaran Valid')
                ->visible(fn($record) => $record->status === 'pending')
                ->action(function ($record) {
                    DB::transaction(function () use ($record) {
                        $rent = $record->rent;

                        // 1. Update status transaksi utama menjadi success
                        $record->update(['status' => 'success']);

                        // 2. Logika bersyarat untuk status sewa (Rent)
                        if ($rent->status === 'pending_verification') {
                            // Skenario Pendaftaran Baru
                            $rent->update([
                                'status' => 'active',
                                'start_date' => now()->addDays(2),
                                'end_date' => now()->addDays(32), // now + 30 hari
                            ]);
                            // 3. Pastikan status lapak (Slot) terkunci menjadi occupied
                            $rent->slot->update(['status' => 'occupied']);
                        } elseif ($rent->status === 'renewal_pending_verification') {
                            // Skenario Perpanjangan Sewa
                            // Ambil end_date lama, lalu tambahkan 30 hari
                            $currentEndDate = Carbon::parse($rent->end_date);

                            $rent->update([
                                'status' => 'active',
                                // start_date tidak diubah
                                'end_date' => $currentEndDate->addDays(32), // end_date lama + 32 hari
                            ]);
                        }
                    });
                    Notification::make()
                        ->title('Pembayaran Berhasil Divalidasi')
                        ->body('Status sewa dan masa berlaku lapak telah diperbarui otomatis.')
                        ->success()
                        ->send();
                }),

            Action::make('reject')
                ->label('Tolak Pembayaran')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Tolak Bukti Pembayaran')
                ->visible(fn($record) => $record->status === 'pending')
                ->form([
                    Textarea::make('reject_reason')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->placeholder('Contoh: Gambar buram, nominal transfer kurang, dll.'),
                ])
                ->action(function ($record, array $data) {
                    DB::transaction(function () use ($record, $data) {
                        $rent = $record->rent;

                        // 1. Update status transaksi menjadi failed dan simpan alasannya
                        $record->update([
                            'status' => 'failed',
                            'reject_reason' => $data['reject_reason'],
                        ]);

                        // 2. Logika bersyarat saat penolakan (Reject)
                        if ($rent->status === 'pending_verification') {
                            // Cek apakah waktu saat ini belum melewati batas reserved_until
                            if (now()->lt(Carbon::parse($rent->reserved_until))) {
                                // Jika belum lewat: status kembali ke pending_payment, lapak tetap reserved
                                $rent->update(['status' => 'pending_payment']);
                            } else {
                                // Jika sudah lewat masa tenggang: sewa gagal, lapak dilepas menjadi available
                                $rent->update(['status' => 'payment_failed']);
                                $rent->slot->update(['status' => 'available']);
                            }
                        } elseif ($rent->status === 'renewal_pending_verification') {
                            //cek apakah lewat masa sewa
                            if (now()->lt(Carbon::parse($rent->end_date))) {
                                //jika belum lewat masa sewa
                                $rent->update(['status' => 'active']);
                            } else {
                                //jika lewat masa sewa
                                $rent->update(['status' => 'payment_failed']);
                                $rent->slot->update(['status' => 'available']);
                            }
                        }
                    });

                    Notification::make()
                        ->title('Pembayaran Ditolak')
                        ->body('Status sistem telah disesuaikan dengan sisa waktu reservasi tenant.')
                        ->warning()
                        ->send();
                }),
        ];
    }
}
