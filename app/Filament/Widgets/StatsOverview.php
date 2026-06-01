<?php

namespace App\Filament\Widgets;

use App\Models\Slot;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pendapatanBulanIni = Transaction::where('status', 'success')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return [
            Stat::make('Lapak Tersedia', Slot::where('status', 'available')->count())
                ->description('Slot kosong yang siap ditawarkan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            // KARTU 2: MENUNGGU VALIDASI
            Stat::make('Menunggu Validasi', Transaction::where('status', 'pending')->count())
                ->description('Bukti transfer yang butuh tinjauan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // KARTU 3: LAPAK AKTIF DISEWA
            Stat::make('Lapak Terisi', Slot::where('status', 'occupied')->count())
                ->description('Total lapak kuliner aktif saat ini')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('danger'),

            // KARTU 4: OMZET BULAN INI
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($pendapatanBulanIni, 0, ',', '.'))
                ->description('Akumulasi transaksi sukses')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
