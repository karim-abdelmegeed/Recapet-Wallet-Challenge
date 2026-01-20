<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'idempotency_key',
        'initiator_user_id',
        'source_wallet_id',
        'destination_wallet_id',
        'amount',
    ];
}
