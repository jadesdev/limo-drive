<?php

namespace App\Services\Pricing;

use App\Models\Fleet;

class PricingCalculator
{
    private const STANDARD_SURCHARGE = 20.00;

    public function calculateDistanceBasedPrice(Fleet $fleet, float $distanceMiles, string $serviceType): array
    {
        $mileageCost = $fleet->rate_per_mile * $distanceMiles;
        $baseFare = $fleet->base_fee + $mileageCost;
        $surcharges = self::STANDARD_SURCHARGE;
        $subtotal = $baseFare + $surcharges;

        $total = $serviceType === 'round_trip' ? $subtotal * 2 : $subtotal;

        return [
            'total' => round($total, 2),
            'breakdown' => [
                'base_fare' => round($baseFare, 2),
                'surcharges' => round($surcharges, 2),
                'total' => round($total, 2),
            ],
        ];
    }

    public function calculateHourlyPrice(Fleet $fleet, int $requestedHours): array
    {
        $bookingHours = max($requestedHours, $fleet->minimum_hours);
        $hourlyRate = $fleet->rate_per_hour * $bookingHours;
        $surcharges = self::STANDARD_SURCHARGE;
        $total = $hourlyRate + $surcharges;

        return [
            'total' => round($total, 2),
            'hours' => $bookingHours,
            'breakdown' => [
                'base_fare' => round($hourlyRate, 2),
                'surcharges' => round($surcharges, 2),
                'hourly_rate' => round($fleet->rate_per_hour, 2),
                'total_hours' => $bookingHours,
                'total' => round($total, 2),
            ],
        ];
    }
}
