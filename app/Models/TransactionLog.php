<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $fillable = ['user_id', 'action', 'status', 'error_message', 'metadata'];
    protected $casts = [
        'metadata' => 'array',
    ];
}
