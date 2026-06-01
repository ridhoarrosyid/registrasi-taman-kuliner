<?php

namespace App\Console\Commands;

use App\Models\Rent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateRentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rent:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis mengecek dan memperbarui status sewa lapak yang kedaluwarsa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan status sewa...');
        DB::transaction(function () {
            // SKENARIO 1: Reservasi (Pending Payment) yang lewat batas waktu
            $expiredReservations = Rent::where('status', 'pending_payment')
                ->where('reserved_until', '<', now())
                ->get();

            foreach ($expiredReservations as $rent) {
                $rent->update(['status' => 'payment_failed']);
                $rent->slot->update(['status' => 'available']); // Lepas lapak
            }
            $this->info("Berhasil membatalkan {$expiredReservations->count()} reservasi kedaluwarsa.");

            // SKENARIO 2: Sewa Aktif yang sudah habis masa sewanya
            $expiredRents = Rent::where('status', 'active')
                ->whereDate('end_date', '<', today())
                ->get();

            foreach ($expiredRents as $rent) {
                $rent->update(['status' => 'expired']);
                $rent->slot->update(['status' => 'available']); // Lepas lapak
            }
            $this->info("Berhasil mengakhiri {$expiredRents->count()} masa sewa aktif.");
        });

        $this->info('Pengecekan status sewa selesai dengan aman!');
    }
}
