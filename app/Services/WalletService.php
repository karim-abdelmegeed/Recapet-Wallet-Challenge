<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\Plateform;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class WalletService
{
    public function deposit(User $user, float $amount, string $idempotencyKey)
    {
        $this->verifyAmount($amount);
        try {
            DB::beginTransaction();
            $wallet = $user->wallet()->lockForUpdate()->first();
            if (!$wallet) {
                throw new \InvalidArgumentException('Error wallet not found');
            }
            $existing = Transaction::where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();
            if ($existing) {
                DB::commit();
                return $existing;
            }

            $transaction =  Transaction::create([
                'type' => 'deposit',
                'status' => 'completed',
                'idempotency_key' => $idempotencyKey,
                'initiator_user_id' => $user->id,
                'source_wallet_id' => null,
                'destination_wallet_id' => $wallet->id,
                'amount' => $amount,
            ]);
            Ledger::create([
                'wallet_id' => $wallet->id,
                'transaction_id' => $transaction->id,
                'source_wallet_id' => null,
                'destination_wallet_id' => $wallet->id,
                'amount' => $amount,
                'entry_type' => 'debit',
                'reference_type' => 'deposit',
                'direction' => 'in'
            ]);
            $wallet->increment('balance', $amount);
            DB::commit();

            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            TransactionLog::create([
                'user_id' => $user->id,
                'action' => 'deposit_failed',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'metadata' => [
                    'amount' => $amount,
                    'idempotency_key' => $idempotencyKey,
                    'balance_before' => $wallet->balance ?? null,
                ],
            ]);
            throw $e;
        }
    }
    public function withdraw(User $user, float $amount, string $idempotencyKey)
    {
        $this->verifyAmount($amount);
        try {
            DB::beginTransaction();
            $existing = Transaction::where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();
            if ($existing) {
                DB::commit();
                return $existing;
            }
            $wallet = $user->wallet()->lockForUpdate()->first();
            if (!$wallet) {
                throw new \InvalidArgumentException('Error wallet not found');
            }
            if ($wallet->balance < $amount) {
                throw new \InvalidArgumentException('insufficient wallet balance');
            }
            $transaction =  Transaction::create([
                'type' => 'withdrawal',
                'status' => 'completed',
                'idempotency_key' => $idempotencyKey,
                'initiator_user_id' => $user->id,
                'source_wallet_id' => $wallet->id,
                'destination_wallet_id' => null,
                'amount' => $amount,
            ]);
            Ledger::create([
                'wallet_id' => $wallet->id,
                'transaction_id' => $transaction->id,
                'source_wallet_id' => $wallet->id,
                'destination_wallet_id' => null,
                'amount' => $amount,
                'entry_type' => 'credit',
                'reference_type' => 'withdrawal',
                'direction' => 'out'
            ]);
            $wallet->decrement('balance', $amount);
            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            TransactionLog::create([
                'user_id' => $user->id,
                'action' => 'withdraw_failed',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'metadata' => [
                    'amount' => $amount,
                    'idempotency_key' => $idempotencyKey,
                    'balance_before' => $wallet->balance ?? null,
                ],
            ]);
            throw $e;
        }
    }

    public function transfer(User $sender, User $receiver, int $amount, string $idempotencyKey)
    {

        $this->verifyAmount($amount);

        return DB::transaction(function () use ($sender, $receiver, $amount, $idempotencyKey) {

            $senderWallet = $sender->wallet()->firstOrFail();
            $receiverWallet = $receiver->wallet()->firstOrFail();

            [$first, $second] = collect([$senderWallet, $receiverWallet])
                ->sortBy('id')
                ->values();

            Wallet::whereKey($first->id)->lockForUpdate()->first();
            Wallet::whereKey($second->id)->lockForUpdate()->first();

            $existing = Transaction::where('idempotency_key', $idempotencyKey)
                ->where('initiator_user_id', $sender->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            if ($senderWallet->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient funds in sender wallet',
                ]);
            }

            $transaction = Transaction::create([
                'type' => 'transfer',
                'status' => 'completed',
                'idempotency_key' => $idempotencyKey,
                'initiator_user_id' => $sender->id,
                'source_wallet_id' => $senderWallet->id,
                'destination_wallet_id' => $receiverWallet->id,
                'amount' => $amount,
            ]);

            Ledger::create([
                'wallet_id' => $senderWallet->id,
                'transaction_id' => $transaction->id,
                'source_wallet_id' => $senderWallet->id,
                'destination_wallet_id' => $receiverWallet->id,
                'amount' => $amount,
                'entry_type' => 'credit',
                'reference_type' => 'transfer',
                'direction' => 'out',
            ]);

            Ledger::create([
                'wallet_id' => $receiverWallet->id,
                'transaction_id' => $transaction->id,
                'source_wallet_id' => $senderWallet->id,
                'destination_wallet_id' => $receiverWallet->id,
                'amount' => $amount,
                'entry_type' => 'debit',
                'reference_type' => 'transfer',
                'direction' => 'in',
            ]);

            if ($this->hasFee($amount)) {
                $fee = $this->calculateFee($amount);
                $plateform = Plateform::first(); //suppose we have only one plateform
                if (!$plateform || !$plateform->wallet) {
                    throw new Exception('Plateform wallet not found');
                }
                Ledger::create([
                    'wallet_id' => $plateform->wallet->id,
                    'transaction_id' => $transaction->id,
                    'source_wallet_id' => $senderWallet->id,
                    'destination_wallet_id' => $plateform->wallet->id,
                    'amount' => $fee,
                    'entry_type' => 'fee',
                    'reference_type' => 'transfer',
                    'direction' => 'in',
                ]);
                $senderWallet->decrement('balance', $amount + $fee);
                $receiverWallet->increment('balance', $amount);
                $plateform->wallet->increment('balance', $fee);
            } else {
                $senderWallet->decrement('balance', $amount);
                $receiverWallet->increment('balance', $amount);
            }


            return $transaction;
        });
    }


    private function hasFee($amount)
    {
        return $amount >= 2500;
    }
    private function calculateFee($amount)
    {
        return (($amount * 0.1) + (2.5 * 100));
    }

    private function verifyAmount($amount)
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }
    }
}
