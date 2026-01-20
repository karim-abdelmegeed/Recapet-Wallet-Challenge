<?php

namespace App\Jobs;

use App\Models\BalanceSnapshot;
use App\Models\Wallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DailyWalletSnapshot implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $snapshotTime = now()->subDay()->startOfDay();

        try {
            $walletCount = Wallet::query()->count();
            if ($walletCount === 0) {
                return;
            }
            Wallet::chunkById(500, function ($wallets) use ($snapshotTime) {
                foreach ($wallets as $wallet) {
                    BalanceSnapshot::firstOrCreate(
                        [
                            'wallet_id' => $wallet->id,
                            'snapshot_time' => $snapshotTime,
                        ],
                        [
                            'balance' => $wallet->balance,
                        ]
                    );
                }
            });

            Log::info('DailyWalletSnapshot finished', [
                'snapshot_time' => $snapshotTime->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('DailyWalletSnapshot failed', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
