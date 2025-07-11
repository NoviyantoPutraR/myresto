<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Download Laporan Excel</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 30px;
        }

        form {
            max-width: 400px;
            margin: auto;
        }

        select,
        button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
        }

        h2 {
            text-align: center;
        }
    </style>
</head>

<body>

    <h2>Download Laporan Transaksi Tahunan (Excel)</h2>

    <form action="{{ route('reports.yearly.excel') }}" method="GET">
        <label for="year">Tahun:</label>
        <select name="year" id="year" required>
            <option value="" disabled selected>-- Pilih Tahun --</option>
            @foreach ($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>

        <button type="submit">Download Excel</button>
    </form>

</body>

</html>
