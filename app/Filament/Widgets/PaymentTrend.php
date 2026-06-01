<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class PaymentTrend extends ChartWidget
{
    protected ?string $heading = 'Tren Pendapatan Bulanan';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = Trend::query(Transaction::where('status', 'success'))
            ->between(
                start: now()->subDays(30),
                end: now(),
            )
            ->perDay()
            ->sum('amount');
        return [
            'datasets' => [
                [
                    'label' => 'Total Pembayaran (IDR)',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                    'tension' => 0.4, // Membuat garis sedikit melengkung (smooth)
                    'borderColor' => '#deff9a',
                    'backgroundColor' => 'rgba(222, 255, 154, 0.1)',
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
