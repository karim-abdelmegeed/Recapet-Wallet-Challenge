<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceSnapshot extends Model
{
    protected $fillable = [
        'wallet_id',
        'balance',
        'snapshot_time',
    ];
    protected $casts = [
        'snapshot_time' => 'datetime',
    ];
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
