<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\WalletDepositRequest;
use App\Http\Requests\WalletTransferRequest;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class WalletController extends Controller
{
    private WalletService $walletService;
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }
    public function deposit(WalletDepositRequest $walletDepositRequest)
    {
        $data = $walletDepositRequest->validated();
        $idempotencyKey = $walletDepositRequest->header('Idempotency-Key');
        if (!$idempotencyKey) {
            return response()->json(['message' => 'Idempotency-Key header is required'], 400);
        }
        $transaction = $this->walletService->deposit(Auth::user(), $data['amount'] * 100, $idempotencyKey);
        return response()->json(['transaction' => $transaction], 200);
    }
    public function withdraw(WalletDepositRequest $walletDepositRequest)
    {
        $data = $walletDepositRequest->validated();
        $idempotencyKey = $walletDepositRequest->header('Idempotency-Key');
        if (!$idempotencyKey) {
            return response()->json(['message' => 'Idempotency-Key header is required'], 400);
        }
        $transaction = $this->walletService->withdraw(Auth::user(), $data['amount'] * 100, $idempotencyKey);
        return response()->json(['transaction' => $transaction], 200);
    }
    public function transfer(WalletTransferRequest $request)
    {
        $data = $request->validated();
        $idempotencyKey = $request->header('Idempotency-Key');
        if (!$idempotencyKey) {
            return response()->json(['message' => 'Idempotency-Key header is required'], 400);
        }

        $receiver = User::findOrFail($data['recipient_user_id']);

        try {
            $transaction = $this->walletService->transfer(
                Auth::user(),
                $receiver,
                $data['amount'] * 100,
                $idempotencyKey,
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json(['transaction' => $transaction], 200);
    }
}
