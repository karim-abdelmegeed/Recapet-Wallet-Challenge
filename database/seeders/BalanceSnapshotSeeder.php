<?php

namespace Database\Seeders;

use App\Models\BalanceSnapshot;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BalanceSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $days = (int) env('BALANCE_SNAPSHOT_DAYS', 30);
        if ($days < 1) {
            $days = 1;
        }

        $start = Carbon::now()->subDays($days)->startOfDay();
        $end = Carbon::now()->subDay()->startOfDay();

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $snapshotTime = $day->copy();

            Wallet::query()->chunkById(500, function ($wallets) use ($snapshotTime) {
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
        }
    }
}
