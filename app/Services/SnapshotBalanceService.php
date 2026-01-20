<?php

namespace App\Services;

use App\Models\BalanceSnapshot;
use App\Models\User;

class SnapshotBalanceService
{
    public function getSnapshotDetails($userId, $date): BalanceSnapshot
    {
        $user = User::find($userId);
        return BalanceSnapshot::with('wallet.owner')
            ->where('wallet_id', $user->wallet->id)
            ->whereDate('snapshot_time', $date)
            ->firstOrFail();
    }
}
