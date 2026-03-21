<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * 統計分析サマリーを取得
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return response()->json([
            'global_trends' => $this->analyticsService->getGlobalTrends(),
            'user_performance' => $this->analyticsService->getUserPerformance($user),
            'recommendations' => $this->analyticsService->getRecommendations($user),
        ]);
    }
}
