<?php

namespace App\Services;

use Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Log;

class GoogleMapsService
{
    /**
     * The Google Maps API Key.
     */
    protected string $apiKey;

    /**
     * The base URL for the Google Maps API.
     */
    protected string $baseUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');

        if (! $this->apiKey) {
            throw new \RuntimeException('Google Maps API key is not configured.');
        }
    }

    /**
     * Get the distance and duration between an origin and a destination.
     *
     * @return array|null An array with distance in miles and duration in seconds, or null on failure.
     *
     * @throws ValidationException
     */
    public function getDistanceMatrix(string $origin, string $destination): ?array
    {
        // Create a cache key based on the origin and destination
        $cacheKey = $this->generateCacheKey($origin, $destination);

        // Try to get from cache first (cache for 24 hours)
        return Cache::remember($cacheKey, 60 * 60 * 24, function () use ($origin, $destination) {
            return $this->fetchDistanceMatrix($origin, $destination);
        });
    }

    /**
     * Generate a cache key for the route
     */
    private function generateCacheKey(string $origin, string $destination): string
    {
        // Normalize addresses to improve cache hit rate
        $normalizedOrigin = strtolower(trim($origin));
        $normalizedDestination = strtolower(trim($destination));

        return 'google_maps_distance:' . md5($normalizedOrigin . '|' . $normalizedDestination);
    }

    /**
     * Fetch distance matrix from Google Maps API
     */
    private function fetchDistanceMatrix(string $origin, string $destination): ?array
    {
        $response = Http::get($this->baseUrl, [
            'origins' => $origin,
            'destinations' => $destination,
            'key' => $this->apiKey,
            'units' => 'imperial', // Returns distance in miles and duration in seconds
        ]);

        if ($response->failed()) {
            Log::error('Google Maps API request failed: ' . $response->body());
            return null;
        }

        $data = $response->json();

        // Check if the overall API call was successful
        if ($data['status'] !== 'OK') {
            Log::error('Google Maps API returned error status: ' . $data['status']);
            return null;
        }

        // Check if we have valid rows and elements
        if (empty($data['rows']) || empty($data['rows'][0]['elements'])) {
            Log::error('Google Maps API returned empty results');
            return null;
        }

        $element = $data['rows'][0]['elements'][0];

        // Handle different element statuses
        if ($element['status'] === 'ZERO_RESULTS') {
            throw ValidationException::withMessages([
                'route' => ['Could not find a valid route between the specified locations. Please check the addresses.'],
            ]);
        }

        if ($element['status'] === 'NOT_FOUND') {
            throw ValidationException::withMessages([
                'route' => ['One or both of the specified locations could not be found.'],
            ]);
        }

        if ($element['status'] !== 'OK') {
            Log::error('Google Maps API element status error: ' . $element['status']);
            throw ValidationException::withMessages([
                'route' => ['Could not calculate route. Please try again later.'],
            ]);
        }

        // Extract distance and duration
        $distanceValue = $element['distance']['value']; // This is in meters
        $durationValue = $element['duration']['value']; // This is in seconds

        // Convert meters to miles (1 meter = 0.000621371 miles)
        $distanceMiles = round($distanceValue * 0.000621371, 2);

        return [
            'distance_miles' => $distanceMiles,
            'duration_seconds' => $durationValue,
            'distance_text' => $element['distance']['text'], // e.g., "25.5 mi"
            'duration_text' => $element['duration']['text'], // e.g., "35 mins"
        ];
    }
}
