<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Inertia\Inertia;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = Wallet::where('owner_id', auth()->id())
            ->where('owner_type', User::class)
            ->first();
        $users = User::where('id', '!=', auth()->id())->get();
        return Inertia::render('Wallet', [
            'wallet' => $wallet,
            'users' => $users
        ]);
    }
}
