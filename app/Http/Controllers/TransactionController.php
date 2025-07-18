<?php

namespace App\Http\Controllers;

use App\Models\Barcode;
use App\Models\Category;
use App\Models\Foods;
use App\Models\Transaction;
use App\Models\TransactionItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;



class TransactionController extends Controller
{
    var $apiInstance = null;

    public function success()
    {
        $externalId = session('external_id');

        if (!$externalId) {
            return redirect()->route('payment.failure');
        }

        $transaction = Transaction::where('external_id', $externalId)->first();

        if (!$transaction) {
            return redirect()->route('payment.failure');
        }

        return view('payment.success', compact('transaction'));
    }


    public function print(Transaction $transaction)
    {
        $transaction->load('items.food', 'barcode', 'user'); // Load semua relasi

        if (request()->has('download')) {
            // Kalau ada parameter 'download', generate PDF
            $pdf = Pdf::loadView('transactions.print', [
                'transaction' => $transaction,
            ]);

            return $pdf->download("bukti-pembayaran-{$transaction->code}.pdf");
        }

        // Kalau tidak ada parameter 'download', tampilkan biasa view-nya
        return view('transactions.print', [
            'transaction' => $transaction,
        ]);
    }




    public function __construct()
    {
        $xenditKey = config('xendit.secret_key');
        Configuration::setXenditKey($xenditKey);
        $this->apiInstance = new InvoiceApi();
    }

    public function handlePayment(Request $request)
    {
        $action = $request->input('action');

        if ($action === 'pay') {
            return $this->processPayment($request);
        }

        if ($action === 'continue') {
            $externalId = session('external_id');

            if (empty($externalId)) {
                return view('payment.failure');
            }


            $transaction = Transaction::where('external_id', $externalId)->first();
            return redirect($transaction->checkout_link);
        }

        abort(400, 'Invalid action.');
    }

    public function processPayment(Request $request)
    {
        $uuid = (string) Str::uuid();

        $sessionToken = session('payment_token');
        $requestToken = $request->input('token');

        if ($sessionToken !== $requestToken) {
            return redirect()->route('payment.failure');
        }

        $cartItems = session('cart_items');
        $name = session('name');
        $phone = session('phone');
        $tableNumber = session('table_number');

        if (empty($cartItems) || empty($name) || empty($phone) || empty($tableNumber)) {
            return response()->json([
                'success' => false,
                'message' => 'Data is empty',
            ], 400);
        }

        $tableNumberId = Barcode::where('table_number', $tableNumber)->first();

        // Cek apakah sudah ada transaksi PENDING atau PAID untuk meja ini dalam 10 menit terakhir
        $recentTransaction = Transaction::where('barcodes_id', $tableNumberId->id)
            ->whereIn('payment_status', ['PENDING', 'PAID'])
            ->where('created_at', '>=', now()->subMinutes(10))
            ->first();

        if ($recentTransaction) {
            return response()->json([
                'success' => false,
                'message' => 'Meja sedang dalam proses transaksi. Silakan pilih meja lain atau tunggu beberapa saat.',
            ], 409); // Conflict
        }


        // $transactionCode = 'TRX_' . mt_rand(100000, 999999);
        $transactionCode = 'TRX_' . date('YmdHis') . '_' . Str::random(5);


        try {
            $subTotal = 0;
            $items = collect($cartItems)
                ->map(function ($item) use (&$subTotal) {
                    $price = isset($item['price_afterdiscount']) ? $item['price_afterdiscount'] : $item['price'];

                    $category = Category::find($item['categories_id'])->name;

                    $foodSubtotal = $price * $item['quantity'];
                    $subTotal += $foodSubtotal;

                    $url = route('product.detail', ['id' => $item['id']]);

                    return [
                        'name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => (int) $price,
                        'category' => $category,
                        'url' => $url,
                    ];
                })
                ->values()
                ->toArray();


            $ppn = 0.10 * $subTotal;

            $description = <<<END
                Pembayaran makanan<br>
                Nomor Meja: {$tableNumber}<br>
                Nama: {$name}<br>
                Nomor Telepon: {$phone}<br>
                Kode Transaksi: {$transactionCode}<br>
                END;

            $createInvoiceRequest = new CreateInvoiceRequest([
                'external_id' => $uuid,
                'amount' => $subTotal + $ppn,
                'description' => $description,
                'invoice_duration' => 3600,
                'currency' => 'IDR',
                'customer' => [
                    'given_names' => $name,
                    'mobile_number' => $phone,
                ],
                'success_redirect_url' => route('payment.success'),
                'failure_redirect_url' => route('payment.failure'),
                'locale' => 'id',
                'items' => $items,
                'fees' => [
                    [
                        'type' => 'PPN 10%',
                        'value' => $ppn,
                    ],
                ],
                "customer_notification_preference" => [
                    "invoice_paid" => [
                        "whatsapp",
                    ]
                ],
            ]);

            $invoice = $this->apiInstance->createInvoice($createInvoiceRequest);

            $transaction = new Transaction();
            $transaction->checkout_link = $invoice['invoice_url'];
            $transaction->payment_method = "PENDING";
            $transaction->phone = $phone;
            $transaction->name = $name;
            $transaction->subtotal = $subTotal;
            $transaction->ppn = $ppn;
            $transaction->barcodes_id = $tableNumberId->id;
            $transaction->total = $subTotal + $ppn;
            $transaction->external_id = $uuid;
            $transaction->code = $transactionCode;
            // $transaction->payment_status = $invoice->getStatus();
            $status = $invoice->getStatus();
            $transaction->payment_status = $status;
            // $transaction->user_id = Auth::id();
            $transaction->user_id = Auth::check() ? Auth::id() : null;
            Log::info('User ID saat transaksi:', ['user_id' => Auth::id(), 'user' => Auth::user()]);

            $transaction->save();


            foreach ($cartItems as $cartItem) {
                $price = isset($cartItem['price_afterdiscount']) ? $cartItem['price_afterdiscount'] : $cartItem['price'];

                TransactionItems::create([
                    'transaction_id' => $transaction->id,
                    'foods_id' => $cartItem['id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $price,
                    'subtotal' => $price * $cartItem['quantity'],
                ]);
            }

            session(['external_id' => $uuid]);
            session(['has_unpaid_transaction' => true]);

            return redirect($invoice['invoice_url']);
        } catch (\Exception $e) {
            Log::error('Failed to create invoice', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->clearSession();
            return view('payment.failure');
        }
    }

    public function paymentStatus($id)
    {
        try {
            $result = $this->apiInstance->getInvoices(null, $id);

            $transaction = Transaction::where('external_id', $id)->firstOrFail();

            if ($transaction->payment_status === 'SETTLED') {
                $this->clearSession();

                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran anda telah berhasil diproses',
                ]);
            }

            $transaction->payment_status = $result[0]['status'];
            $transaction->payment_method = $result[0]['payment_method'];

            $transaction->save();

            $this->clearSession();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get invoice', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('payment.failure');
        }
    }

    public function handleWebhook(Request $request)
    {
        $webhookToken = $request->header('x-callback-token');

        $expectedToken = config('xendit.webhook_token');

        if ($webhookToken !== $expectedToken) {
            return response()->json([
                'message' => 'Unauthorized webhook request.',
            ], 401);
        }

        try {
            $data = $request->all();
            $external_id = $data['external_id'];
            $status = $data['status'];
            $payment_method = $data['payment_method'];


            // $transaction = Transaction::where('external_id', $external_id)->first();
            $transaction = Transaction::where('external_id', $external_id)->first();

            if (!$transaction) {
                return response()->json(['message' => 'Transaction not found.'], 404);
            }

            $transaction->payment_status = $status;
            $transaction->payment_method = $payment_method;
            // $transaction->updated_at = $data['updated'];
            $transaction->updated_at = \Carbon\Carbon::parse($data['updated']);
            $transaction->save();

            $this->clearSession();


            return response()->json([
                'code' => 200,
                'message' => 'Webhook received',
                'status' => $status,
                'payment_method' => $payment_method,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to handle webhook', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to handle webhook.',
            ], 500);
        }
    }

    public function clearSession()
    {
        Session::forget(['name', 'external_id', 'has_unpaid_transaction', 'cart_items', 'payment_token']);
        Session::save();
    }
}
