<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionPrintController extends Controller
{
    public function print(Transaction $transaction)
    {
        $pdf = Pdf::loadView('transactions.print', ['transaction' => $transaction]);

        return $pdf->download('bukti_pembayaran_' . $transaction->code . '.pdf');
    }
}
