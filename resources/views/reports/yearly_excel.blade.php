<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background-color: #f2f2f2;
        }

        .month-header {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 5px;
        }

        .total-row {
            background-color: #d9edf7;
            font-weight: bold;
        }

        .no-data {
            font-style: italic;
            color: #666;
        }
    </style>
</head>

<body>

    <h2>Laporan Transaksi Tahun {{ $year }}</h2>

    @foreach ($monthlyTransactions as $month => $transactions)
        @php
            $monthName = \Carbon\Carbon::create($year, $month)->translatedFormat('F');
        @endphp

        <p class="month-header">{{ strtoupper($monthName) }}</p>

        @if ($transactions->isEmpty())
            <p class="no-data">Tidak ada transaksi pada bulan ini.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $trx)
                        <tr>
                            <td>{{ $trx->code }}</td>
                            <td>{{ $trx->name }}</td>
                            <td>{{ $trx->payment_method }}</td>
                            <td>{{ $trx->payment_status }}</td>
                            <td>Rp{{ number_format($trx->total, 0, ',', '.') }}</td>
                            <td>{{ $trx->created_at->format('d-m-Y') }}</td>
                        </tr>
                    @endforeach

                    <tr class="total-row">
                        <td colspan="4">Total Bulan {{ $monthName }}</td>
                        <td colspan="2">Rp{{ number_format($transactions->sum('total'), 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    @endforeach

</body>

</html>
