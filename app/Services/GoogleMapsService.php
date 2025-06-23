<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Log;

class GoogleMapsService
{
    /**
     * The Google Maps API Key.
     *
     * @var string
     */
    protected string $apiKey;

    /**
     * The base URL for the Google Maps API.
     *
     * @var string
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
     * @param  string  $origin
     * @param  string  $destination
     * @return array|null An array with 'distance' (in meters) and 'duration' (in seconds), or null on failure.
     *
     * @throws ValidationException
     */
    public function getDistanceMatrix(string $origin, string $destination): ?array
    {
        $response = Http::get($this->baseUrl, [
            'origins' => $origin,
            'destinations' => $destination,
            'key' => $this->apiKey,
            'units' => 'metric', // Use metric for meters/km. Use 'imperial' for miles/feet.
        ]);

        if ($response->failed()) {
            Log::error('Google Maps API request failed: ' . $response->body());
            return null;
        }

        $data = $response->json();

        if ($data['status'] !== 'OK' || empty($data['rows'][0]['elements'][0]['status'] === 'ZERO_RESULTS')) {
            throw ValidationException::withMessages([
                'route' => ['Could not find a valid route between the specified locations. Please check the addresses.'],
            ]);
        }

        if ($data['rows'][0]['elements'][0]['status'] !== 'OK') {
            throw ValidationException::withMessages([
                'route' => ['Could not find a valid route for one of the locations specified.'],
            ]);
        }


        $element = $data['rows'][0]['elements'][0];

        return [
            'distance_meters' => $element['distance']['value'],
            'duration_seconds' => $element['duration']['value'],
            'distance_text' => $element['distance']['text'], // e.g., "25.5 km"
            'duration_text' => $element['duration']['text'], // e.g., "35 mins"
        ];
    }
}
