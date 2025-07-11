<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyTransactionChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Transaksi per Bulan';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $year = now()->year;

        $transactions = Transaction::selectRaw('MONTH(created_at) as month, SUM(total) as total')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $data = [];

        for ($i = 1; $i <= 12; $i++) {
            $data[] = $transactions[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Transaksi (Rp)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59,130,246,0.5)',
                    'borderColor' => 'rgba(59,130,246,1)',
                ],
            ],
            'labels' => [
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'Mei',
                'Jun',
                'Jul',
                'Agu',
                'Sep',
                'Okt',
                'Nov',
                'Des',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
