<?php

namespace App\Http\Controllers;

use App\Models\Barcode;
use Illuminate\Http\Request;

class QRController extends Controller
{
    /**
     * Menyimpan hasil scan dari halaman /scan
     */
    public function storeResult(Request $request)
    {
        $request->validate([
            'table_number' => 'required|string',
        ]);

        $table = Barcode::where('table_number', $request->table_number)->first();

        if ($table) {
            session(['table_number' => $table->table_number]);
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Nomor meja tidak ditemukan.'], 404);
        }
    }

    /**
     * Menangani akses langsung via QR: /{tableNumber}
     */
    public function checkCode($code)
    {
        $table = Barcode::where('table_number', $code)->first();

        if (!$table) {
            return view('invalid', [
                'message' => 'Nomor meja tidak ditemukan. Silakan scan QR yang valid.',
            ]);
        }

        // Cek apakah ada transaksi aktif (PENDING/PAID) dalam 10 menit terakhir
        $recentTransaction = \App\Models\Transaction::where('barcodes_id', $table->id)
            ->whereIn('payment_status', ['PENDING', 'PAID'])
            ->where('created_at', '>=', now()->subMinutes(10))
            ->first();

        if ($recentTransaction) {
            return view('errors.meja_dipakai', [
                'message' => 'Meja ini sedang digunakan, silakan tunggu 10 menit atau hubungi petugas.'
            ]);
        }

        session(['table_number' => $table->table_number]);
        return redirect()->route('home');
    }
}
