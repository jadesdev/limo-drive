<?php

namespace App\Services\Route;

use App\Services\GoogleMapsService;
use Illuminate\Validation\ValidationException;

class RouteService
{
    public function __construct(
        private bool $demoMode = true
    ) {}

    public function getRouteInfo(array $data): array
    {
        if ($this->demoMode) {
            return $this->getSimulatedTripInfo($data['service_type']);
        }

        return $this->getRealRouteInfo($data['pickup_address'], $data['dropoff_address']);
    }

    private function getRealRouteInfo(string $pickupAddress, string $dropoffAddress): array
    {
        $googleMapsService = app(GoogleMapsService::class);
        $routeInfo = $googleMapsService->getDistanceMatrix($pickupAddress, $dropoffAddress);

        if (!$routeInfo) {
            throw ValidationException::withMessages([
                'route' => ['We could not calculate the route at this time. Please try again later.'],
            ]);
        }

        return $routeInfo;
    }

    private function getSimulatedTripInfo(string $serviceType): array
    {
        $distanceMiles = rand(5, 60);
        $durationSeconds = rand(60, 3600);
        $hours = floor($durationSeconds / 3600);
        $minutes = round(($durationSeconds % 3600) / 60);
        $durationText = ($hours > 0 ? "{$hours} hours " : '') . "{$minutes} mins";

        return [
            'distance_miles' => $distanceMiles,
            'duration_seconds' => $durationSeconds,
            'duration_text' => $durationText,
            'distance_text' => $distanceMiles . ' miles',
        ];
    }
}
