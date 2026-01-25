<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaveBalanceController extends Controller
{
    /**
     * Constructor met dependency injection
     */
    public function __construct(protected LeaveBalanceService $leaveBalanceService)
    {
    }

    /**
     * Haal het verlofsaldo op voor de ingelogde gebruiker
     *
     * GET /api/me/leave-balance
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }

        // Haal het saldo op voor huidige jaar
        $balance = $this->leaveBalanceService->getRemainingForUser($user);

        return response()->json([
            'status' => 'ok',
            'data' => $balance,
        ]);
    }

    /**
     * Haal het verlofsaldo op voor een specifieke gebruiker (voor HR/managers)
     * TODO: Voeg autorisatiecheck toe
     *
     * GET /api/users/{userId}/leave-balance
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function forUser(int $userId): JsonResponse
    {
        // TODO: Autorisatiecheck toevoegen (alleen managers/HR)

        $balance = $this->leaveBalanceService->getRemainingForUser($userId);

        return response()->json([
            'status' => 'ok',
            'data' => $balance,
        ]);
    }
}
