<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(protected DashboardService $dashboardService) {}

    /**
     * Get summary data
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'sometimes|string|in:today,this_week,this_month',
        ]);

        $period = $validated['period'] ?? 'this_week';
        $cacheKey = 'dashboard_summary:' . $period;
        $summaryData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($period) {
            return $this->dashboardService->getDashboardSummary($period);
        });

        return $this->dataResponse('Dashboard summary retrieved successfully.', $summaryData);
    }

    /**
     * Get chart data
     */
    public function chart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'sometimes|string|in:this_week,last_7_days,last_14_days,this_month,last_30_days,this_year',
            'start_date' => 'nullable|string|date_format:Y-m-d|before_or_equal:end_date',
            'end_date' => 'nullable|string|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $period = $validated['period'] ?? 'this_week';
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $cacheKey = 'dashboard_chart:' . $period . ':' . ($startDate ?? 'null') . ':' . ($endDate ?? 'null');
        $chartData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($period, $startDate, $endDate) {
            return $this->dashboardService->getChartData($period, $startDate, $endDate);
        });

        return $this->dataResponse('Chart data retrieved successfully', $chartData);
    }
}
