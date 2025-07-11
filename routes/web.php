<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\QRController;
use App\Http\Middleware\CheckTableNumber;
use App\Livewire\Pages\AllFoodPage;
use App\Livewire\Pages\CartPage;
use App\Livewire\Pages\CheckoutPage;
use App\Livewire\Pages\DetailPage;
use App\Livewire\Pages\FavoritePage;
use App\Livewire\Pages\PromoPage;
use App\Livewire\Pages\HomePage;
use App\Livewire\Pages\PaymentFailurePage;
use App\Livewire\Pages\PaymentSuccessPage;
use App\Livewire\Pages\ScanPage;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use App\Http\Controllers\TransactionReportController;
use App\Http\Controllers\TransactionPrintController;

Route::get('/dashboard', function () {
    return redirect('/admin');
});

// Generate dan download PDF transaksi
Route::get('/transactions/{transaction}/print', [TransactionPrintController::class, 'print'])->name('transactions.print');
//untuk pelanggan cetak bukti pembayaran

Route::get('/payment/success', [TransactionController::class, 'success'])->name('payment.success');
Route::get('/payment/failure', function () {
    return view('payment.failure');
})->name('payment.failure');

Route::get('/transactions/{transaction}/print', [TransactionController::class, 'print'])->name('transactions.print');




// Menampilkan form
Route::get('/transactions/report/form', [TransactionReportController::class, 'yearlyForm'])->name('transactions.report.form');

// Generate dan download PDF
Route::get('/transactions/report/yearly', [TransactionReportController::class, 'yearlyReport'])->name('transactions.report.yearly');

Route::get('/reports/yearly/excel', [TransactionReportController::class, 'yearlyExcel'])->name('reports.yearly.excel');



Route::middleware(CheckTableNumber::class)->group(function () {
    // Beranda | Home
    Route::get('/', HomePage::class)->name('home');
    // Semua Makanan | All Food
    Route::get('/food', AllFoodPage::class)->name('product.index');
    // Makanan Favorit | Favorite Food
    Route::get('/food/favorite', FavoritePage::class)->name('product.favorite');
    // Makanan Promo | Today's Promo
    Route::get('/food/promo', PromoPage::class)->name('product.promo');
    // Detail Makanan | Food Details
    Route::get('/food/{id}', DetailPage::class)->name('product.detail');
});

Route::middleware(CheckTableNumber::class)->controller(TransactionController::class)->group(function () {
    Route::get('/cart', CartPage::class)->name('payment.cart');
    Route::get('/checkout', CheckoutPage::class)->name('payment.checkout');

    // Proses pembayaran
    Route::middleware('throttle:10,1')->post('/payment', 'handlePayment')->name('payment');
    Route::get('/payment', function () {
        abort(404);
    });

    // Status pembayaran
    Route::get('/payment/status/{id}', 'paymentStatus')->name('payment.status');
    Route::get('/payment/failure', PaymentFailurePage::class)->name('payment.failure');
});

// Webhook update pembayaran
Route::post('/payment/webhook', [TransactionController::class, 'handleWebhook'])->name('payment.webhook');

Route::controller(QRController::class)->group(function () {
    Route::post('/store-qr-result', 'storeResult')->name('product.scan.store');
    // Scanner
    Route::get('/scan', ScanPage::class)->name('product.scan');
    Route::get('/{tableNumber}', 'checkCode')->name('product.scan.table');
});
