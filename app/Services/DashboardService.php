<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Fleet;
use App\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class DashboardService
{

    /**
     * Gathers all summary data for the admin dashboard.
     *
     * @param string $period The time period for stats ('this_week', 'this_month', 'today').
     * @return array
     */
    public function getDashboardSummary(string $period): array
    {
        $dateRange = $this->getDateRangeForPeriod($period);
        $previousDateRange = $this->getPreviousDateRange($dateRange);

        return [
            'booking_summary' => $this->getBookingSummary(),
            'stats' => $this->getKpiStats($dateRange, $previousDateRange),
            'recently_added_fleet' => $this->getRecentlyAddedFleet(),
        ];
    }


    /**
     * Gathers and formats data for the income chart.
     */
    public function getChartData(string $period, ?string $startDate, ?string $endDate): array
    {
        [$start, $end] = $this->resolveDateRange($period, $startDate, $endDate);

        $incomeData = Payment::whereIn('status', ['paid', 'succeeded', 'completed'])
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            ])
            ->pluck('total', 'date');

        $chartSeries = [];
        $datePeriod = CarbonPeriod::create($start, $end);

        foreach ($datePeriod as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartSeries[] = [
                'date' => $formattedDate,
                'income' => (float) ($incomeData[$formattedDate] ?? 0),
            ];
        }

        return [
            'total_income' => round($incomeData->sum(), 2),
            'period_label' => $this->getPeriodLabel($period),
            'series' => $chartSeries,
        ];
    }


    /**
     * Helper to resolve the date range from period or custom dates.
     */
    private function resolveDateRange(string $period, ?string $startDate, ?string $endDate): array
    {
        if ($startDate && $endDate) {
            return [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()];
        }

        return match ($period) {
            'today' => [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()],
            'last_7_days' => [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()],
            'last_14_days' => [Carbon::now()->subDays(13)->startOfDay(), Carbon::now()->endOfDay()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_30_days' => [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()], // 'this_week'
        };
    }

    /**
     * Helper to get a human-readable label for the period.
     */
    private function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Today',
            'last_7_days' => 'Last 7 Days',
            'last_14_days' => 'Last 14 Days',
            'this_month' => 'This Month',
            'last_30_days' => 'Last 30 Days',
            'this_year' => 'This Year',
            default => 'This Week',
        };
    }
    /**
     * Get the main booking summary counts.
     */
    private function getBookingSummary(): array
    {
        return [
            'total' => Booking::count(),
            'in_rental' => Booking::where('status', 'in_progress')->count(),
            'upcoming' => Booking::where('status', 'confirmed')
                ->where('pickup_datetime', '>', now())
                ->count(),
        ];
    }

    /**
     * Get the main KPI stats with percentage change.
     */
    private function getKpiStats(array $currentRange, array $previousRange): array
    {
        $currentReservations = Booking::whereBetween('created_at', $currentRange)->count();
        $previousReservations = Booking::whereBetween('created_at', $previousRange)->count();

        $currentEarnings = Payment::where('status', 'completed')
            ->whereBetween('created_at', $currentRange)
            ->sum('amount');
        $previousEarnings = Payment::where('status', 'completed')
            ->whereBetween('created_at', $previousRange)
            ->sum('amount');

        $newFleetsCurrent = Fleet::whereBetween('created_at', $currentRange)->count();
        $newFleetsPrevious = Fleet::whereBetween('created_at', $previousRange)->count();


        return [
            'total_reservations' => [
                'value' => $currentReservations,
                'change_percentage' => $this->calculatePercentageChange($currentReservations, $previousReservations),
            ],
            'total_earnings' => [
                'value' => round($currentEarnings, 2),
                'change_percentage' => $this->calculatePercentageChange($currentEarnings, $previousEarnings),
            ],
            'total_fleets' => [
                'value' => Fleet::count(),
                'change_percentage' => $this->calculatePercentageChange($newFleetsCurrent, $newFleetsPrevious),
            ]
        ];
    }

    /**
     * Get the most recently added fleet.
     */
    private function getRecentlyAddedFleet()
    {
        $fleet = Fleet::where('is_active', true)->latest('created_at')->first();

        if (!$fleet) {
            return null;
        }

        return  [
            'id' => $fleet->id,
            'name' => $fleet->name,
            'thumbnail_url' => $fleet->thumbnail_url,
            'passengers' => $fleet->seats,
            'bags' => $fleet->bags,
            'image_urls' => $fleet->image_urls,
            'description' => $fleet->description,
        ];
    }


    private function getDateRangeForPeriod(string $period): array
    {
        return match ($period) {
            'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
        };
    }

    private function getPreviousDateRange(array $currentRange): array
    {
        $startDate = $currentRange[0];
        $endDate = $currentRange[1];
        $diffInDays = $endDate->diffInDays($startDate);

        return [
            $startDate->clone()->subDays($diffInDays + 1),
            $endDate->clone()->subDays($diffInDays + 1),
        ];
    }

    private function calculatePercentageChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        $change = (($current - $previous) / $previous) * 100;
        return round($change, 2);
    }
}
