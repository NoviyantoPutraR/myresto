<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function barcode()
    {
        return $this->belongsTo(Barcode::class, 'barcodes_id');
    }

    // Transaction.php
    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function items()
    {
        return $this->hasMany(TransactionItems::class, 'transaction_id');
    }
}
