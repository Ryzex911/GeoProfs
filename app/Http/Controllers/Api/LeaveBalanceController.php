<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    protected LeaveBalanceService $leaveBalanceService;

    public function __construct(LeaveBalanceService $leaveBalanceService)
    {
        $this->leaveBalanceService = $leaveBalanceService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get leave balance for the authenticated user
     * US007: Saldo ophalen voor gebruiker
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $year = $request->query('year', date('Y'));

        $balance = $this->leaveBalanceService->getRemainingForUser($user, (int) $year);

        return response()->json($balance);
    }
}
