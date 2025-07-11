<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bukti Pembayaran - {{ $transaction->code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header,
        .footer {
            text-align: center;
        }

        .header h2 {
            margin: 0;
            margin-bottom: 10px;
        }

        .info,
        .totals {
            margin-top: 20px;
        }

        .info p,
        .totals p {
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        th {
            background-color: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Bukti Pembayaran</h2>
        <p><strong>Kode Transaksi:</strong> {{ $transaction->code }}</p>
    </div>

    <div class="info">
        <p><strong>Nama:</strong> {{ $transaction->name }}</p>
        <p><strong>No HP:</strong> {{ $transaction->phone }}</p>
        {{-- <p><strong>Kasir:</strong> {{ $transaction->user->name ?? '-' }}</p> --}}
        <p><strong>Meja:</strong> {{ $transaction->barcode->table_number ?? '-' }}</p>
        <p><strong>Metode Pembayaran:</strong> {{ $transaction->payment_method }}</p>
        <p><strong>Status Pembayaran:</strong> {{ $transaction->payment_status }}</p>
        <p><strong>Tanggal:</strong> {{ $transaction->created_at->format('d-m-Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Menu</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->food->name ?? '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p><strong>Subtotal:</strong> Rp{{ number_format($transaction->subtotal, 0, ',', '.') }}</p>
        <p><strong>PPN:</strong> Rp{{ number_format($transaction->ppn, 0, ',', '.') }}</p>
        <p><strong>Total:</strong> <u>Rp{{ number_format($transaction->total, 0, ',', '.') }}</u></p>
    </div>

    <div class="footer">
        <p>Terima kasih telah berkunjung.</p>
    </div>
</body>

</html>
