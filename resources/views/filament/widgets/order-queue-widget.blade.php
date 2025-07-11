<div wire:poll.10s="loadOrders">
    <style>
        @keyframes flash {
            0% {
                background-color: #dcfce7;
            }

            100% {
                background-color: white;
            }
        }

        .flash-new {
            animation: flash 1s ease-in-out;
        }
    </style>

    <div class="space-y-4">
        @php
            $latestOrderId = $orders[0]['id'] ?? null;
        @endphp

        @forelse ($orders as $index => $order)
            @php
                $isNew = $order['id'] === $latestOrderId;
            @endphp

            <div
                class="p-4 border rounded-lg shadow-sm bg-white dark:bg-gray-800 transition {{ $isNew ? 'flash-new' : '' }}">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-lg font-semibold">Antrian #{{ $index + 1 }}</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <strong>Nama:</strong> {{ $order['name'] ?? 'Pelanggan' }}<br>
                            <strong>Meja:</strong> {{ $order['table_number'] ?? '-' }}
                        </p>
                    </div>

                    <div class="flex gap-2 items-center">
                        @if (!$order['is_sent'])
                            <x-filament::button wire:click="markAsSent({{ $order['id'] }})" color="success"
                                size="sm">
                                Tandai Dikirim
                            </x-filament::button>
                        @else
                            <span class="text-green-600 font-medium">Sudah dikirim</span>
                        @endif
                    </div>

                </div>
            </div>

        @empty
            <p class="text-center text-gray-500 dark:text-gray-400">Belum ada order hari ini.</p>
        @endforelse
    </div>

    {{-- Notifikasi Javascript --}}
    <script>
        window.addEventListener('new-order', event => {
            window.Filament?.notification?.send({
                title: 'Order Baru',
                body: event.detail.message,
                type: 'success',
                duration: 5000,
            });
        });
    </script>
</div>
