<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'wallet_id',
        'amount',
        'direction',
        'source_wallet_id',
        'destination_wallet_id',
        'entry_type',
        'reference_type'
    ];
}
