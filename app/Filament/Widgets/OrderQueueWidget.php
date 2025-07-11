<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\Widget;
use Filament\Notifications\Notification;

class OrderQueueWidget extends Widget
{
    protected static string $view = 'filament.widgets.order-queue-widget';

    public $orders = [];
    public $lastOrderCount = 0;

    protected static ?int $pollingInterval = 10; // Refresh setiap 10 detik

    public function mount()
    {
        $this->loadOrders();
        $this->lastOrderCount = count($this->orders);
    }

    public function loadOrders()
    {
        // Eager load barcode relationship
        $currentOrders = Transaction::with('barcode')
            ->where('payment_status', 'PAID')  // Menampilkan hanya order yang sudah dibayar
            ->where('is_sent', false)         // Order yang belum dikirim
            ->whereDate('created_at', today()) // Order hari ini
            ->oldest()
            ->get();

        // Notifikasi jika order bertambah
        if ($this->lastOrderCount < $currentOrders->count()) {
            // Kirim Toast Notification saat order baru masuk
            Notification::make()
                ->title('Order Baru Masuk!')
                ->body('Ada order baru yang perlu diproses.')
                ->success()
                ->send();
        }

        $this->lastOrderCount = $currentOrders->count();

        // Mapping order ke array agar bisa dipakai di blade
        $this->orders = $currentOrders->map(function ($order) {
            return [
                'id' => $order->id,
                'name' => $order->name,
                'is_sent' => $order->is_sent,
                'table_number' => $order->barcode->table_number ?? '-',
            ];
        })->toArray();
    }

    public function markAsSent($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update(['is_sent' => true]);
        $this->loadOrders();
    }
}
