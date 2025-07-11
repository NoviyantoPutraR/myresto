<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Exports\YearlyTransactionExport;
use Maatwebsite\Excel\Facades\Excel;

class TransactionReportController extends Controller
{
    public function monthlyReport()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $transactions = Transaction::whereBetween('created_at', [$start, $end])->get();

        $pdf = Pdf::loadView('reports.monthly', compact('transactions'));

        return $pdf->download('laporan-transaksi-' . now()->format('F-Y') . '.pdf');
    }

    public function yearlyForm()
    {
        // Ambil tahun-tahun yang tersedia di database transaksi
        $years = Transaction::selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year');

        return view('reports.form', compact('years'));
    }

    public function yearlyReport(Request $request)
    {
        $year = $request->input('year', now()->year);

        $monthlyTransactions = [];

        foreach (range(1, 12) as $month) {
            $start = Carbon::create($year, $month)->startOfMonth();
            $end = Carbon::create($year, $month)->endOfMonth();

            $monthlyTransactions[$month] = Transaction::whereBetween('created_at', [$start, $end])->get();
        }

        $pdf = Pdf::loadView('reports.yearly', [
            'monthlyTransactions' => $monthlyTransactions,
            'year' => $year,
        ]);

        return $pdf->download("laporan-transaksi-{$year}.pdf");
    }


    public function yearlyExcel(Request $request)
    {
        $year = $request->input('year', now()->year);

        $monthlyTransactions = [];

        foreach (range(1, 12) as $month) {
            $start = Carbon::create($year, $month)->startOfMonth();
            $end = Carbon::create($year, $month)->endOfMonth();

            $monthlyTransactions[$month] = Transaction::whereBetween('created_at', [$start, $end])->get();
        }

        return Excel::download(
            new YearlyTransactionExport($year, $monthlyTransactions),
            "laporan-transaksi-{$year}.xlsx"
        );
    }
}
