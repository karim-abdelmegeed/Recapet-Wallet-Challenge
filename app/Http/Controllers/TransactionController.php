<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where('initiator_user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(5);
        return Inertia::render('Transactions', [
            'transactions' => $transactions
        ]);
    }
}
