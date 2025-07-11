<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Illuminate\Support\Facades\URL;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;



    protected function getHeaderActions(): array
    {
        return [
            Action::make('Cetak Laporan Transaksi')
                ->url(fn() => route('transactions.report.form'))
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->openUrlInNewTab()

        ];
    }
}
