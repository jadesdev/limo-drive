<?php

namespace App\Services\Route;

use App\Services\GoogleMapsService;
use Cache;
use Illuminate\Validation\ValidationException;

class RouteService
{
    private bool $demoMode;

    public function __construct()
    {
        $this->demoMode = config('services.google_maps.demo', false);
    }

    public function getRouteInfo(array $data): array
    {
        if ($this->demoMode) {
            return $this->getSimulatedTripInfo($data['service_type']);
        }

        return $this->getCachedRouteInfo($data['pickup_address'], $data['dropoff_address']);
    }

    /**
     * Get route info with caching layer
     */
    private function getCachedRouteInfo(string $pickupAddress, string $dropoffAddress): array
    {
        $cacheKey = $this->generateRouteCacheKey($pickupAddress, $dropoffAddress);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($pickupAddress, $dropoffAddress) {
            return $this->getRealRouteInfo($pickupAddress, $dropoffAddress);
        });
    }

    /**
     * Generate cache key for route
     */
    private function generateRouteCacheKey(string $pickup, string $dropoff): string
    {
        $normalizedPickup = $this->normalizeAddress($pickup);
        $normalizedDropoff = $this->normalizeAddress($dropoff);

        return 'route_info:' . hash('sha256', $normalizedPickup . '|' . $normalizedDropoff);
    }

    /**
     * Normalize address for consistent caching
     */
    private function normalizeAddress(string $address): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $address)));
    }

    private function getRealRouteInfo(string $pickupAddress, string $dropoffAddress): array
    {
        $googleMapsService = app(GoogleMapsService::class);
        $routeInfo = $googleMapsService->getDistanceMatrix($pickupAddress, $dropoffAddress);

        if (! $routeInfo) {
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
