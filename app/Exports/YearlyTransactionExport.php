<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class YearlyTransactionExport implements FromView
{
    protected $year;
    protected $monthlyTransactions;

    public function __construct($year, $monthlyTransactions)
    {
        $this->year = $year;
        $this->monthlyTransactions = $monthlyTransactions;
    }

    public function view(): View
    {
        return view('reports.yearly_excel', [
            'year' => $this->year,
            'monthlyTransactions' => $this->monthlyTransactions
        ]);
    }
}
