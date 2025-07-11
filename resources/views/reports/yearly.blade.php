<!DOCTYPE html>
<html>

<head>
    <title>Laporan Transaksi Tahunan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        h2 {
            margin-top: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 30px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #f0f0f0;
        }

        .month-title {
            margin-top: 50px;
            font-size: 16px;
            font-weight: bold;
        }

        .no-data {
            font-style: italic;
            color: #666;
        }
    </style>
</head>

<body>
    <h1>Laporan Transaksi Tahun {{ $year }}</h1>

    @foreach ($monthlyTransactions as $month => $transactions)
        <h2 class="month-title">{{ \Carbon\Carbon::create($year, $month)->translatedFormat('F') }}</h2>

        @if ($transactions->isEmpty())
            <p class="no-data">Tidak ada transaksi.</p>
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
                </tbody>
            </table>
        @endif
    @endforeach
</body>

</html>
