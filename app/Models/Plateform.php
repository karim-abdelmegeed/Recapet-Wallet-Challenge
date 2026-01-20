<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plateform extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function wallet()
    {
        return $this->morphOne(Wallet::class, 'owner');
    }
}
