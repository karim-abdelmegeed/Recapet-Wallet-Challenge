<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\BalanceSnapshotRequest;
use App\Services\SnapshotBalanceService;
use Illuminate\Support\Facades\Auth;

class SnapshotBalanceController extends Controller
{
    private SnapshotBalanceService $balanceSnapshotService;
    public function __construct(SnapshotBalanceService $balanceSnapshotService)
    {
        $this->balanceSnapshotService = $balanceSnapshotService;
    }
    public function index(BalanceSnapshotRequest $balanceSnapshotRequest)
    {
        $snapshot = $this->balanceSnapshotService->getSnapshotDetails(
            Auth::id(),
            $balanceSnapshotRequest->date
        );
        return response()->json(['snapshot' => $snapshot], 200);
    }
}
