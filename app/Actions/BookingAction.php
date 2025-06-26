<?php

namespace App\Actions;

use App\Http\Resources\Booking\DistanceBasedQuoteResource;
use App\Http\Resources\Booking\HourlyBasedQuoteResource;
use App\Models\Booking;
use App\Models\Fleet;
use App\Services\GoogleMapsService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Str;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class BookingAction
{
    /**
     * Get a quote for a trip based on service type and other parameters.
     *
     * @throws ValidationException
     */
    public function getQuote(array $data)
    {
        $serviceType = $data['service_type'] ?? 'point_to_point';

        $distanceServices = ['point_to_point', 'airport_pickup', 'airport_transfer', 'round_trip'];
        if (in_array($serviceType, $distanceServices)) {
            return $this->getDistanceBasedQuote($data);
        }

        $hourlyServices = ['wedding', 'event', 'other'];
        if (in_array($serviceType, $hourlyServices)) {
            return $this->getHourlyQuote($data);
        }

        throw ValidationException::withMessages([
            'service_type' => ['The selected service type is not supported for quoting.'],
        ]);
    }

    /**
     * Calculate quotes for distance-based services.
     */
    protected function getDistanceBasedQuote(array $data)
    {
        $demoMode = true;
        if ($demoMode) {
            $routeInfo = $this->getSimulatedTripInfo($data['service_type']);
        } else {
            $googleMapsService = app(GoogleMapsService::class);
            $routeInfo = $googleMapsService->getDistanceMatrix(
                $data['pickup_address'],
                $data['dropoff_address']
            );
            if (! $routeInfo) {
                throw ValidationException::withMessages([
                    'route' => ['We could not calculate the route at this time. Please try again later.'],
                ]);
            }
        }

        $distanceInMiles = $routeInfo['distance_miles'];
        $durationInMinutes = round($routeInfo['duration_seconds'] / 60);
        $durationText = $routeInfo['duration_text'] ?? null;

        $fleets = Fleet::active()
            ->where('seats', '>=', $data['passenger_count'])
            ->where('bags', '>=', $data['bag_count'])
            ->get();

        if ($fleets->isEmpty()) {
            throw ValidationException::withMessages([
                'capacity' => ['Sorry, we have no vehicles available that can accommodate your party size.'],
            ]);
        }

        $vehicles = $fleets->map(function (Fleet $fleet) use ($distanceInMiles, $data, $durationText, $durationInMinutes) {

            $milageCost = $fleet->rate_per_mile * $distanceInMiles;
            $baseFare = $fleet->base_fee + $milageCost;
            $surcharges = 20; // fixed 20$
            $totalPrice = $baseFare + $surcharges;

            if ($data['service_type'] === 'round_trip') {
                $totalPrice *= 2;
            }
            return [
                'id' => $fleet->id,
                'name' => $fleet->name,
                'slug' => $fleet->slug,
                'seats' => $fleet->seats,
                'bags' => $fleet->bags,
                'thumbnail_url' => $fleet->thumbnail_url,
                'image_urls' => $fleet->image_urls,
                'price' => round($totalPrice, 2),
                'estimated_duration' => $durationText,
                'price_breakdown' => [
                    'base_fare' => round($baseFare, 2),
                    'surcharges' => round($surcharges, 2),
                    'total' => round($totalPrice, 2)
                ],
                'distance' => [
                    'miles' => $distanceInMiles,
                    'minutes' => $durationInMinutes,
                    'description' => $durationText,
                ]
            ];
        });

        return DistanceBasedQuoteResource::collection($vehicles);
    }

    /**
     * Calculate quotes for hourly-based services.
     */
    protected function getHourlyQuote(array $data)
    {
        if (empty($data['duration_hours'])) {
            throw ValidationException::withMessages([
                'duration_hours' => ['Booking duration is required for this service type.'],
            ]);
        }

        $hours = (int) $data['duration_hours'];

        $fleets = Fleet::active()
            ->where('seats', '>=', $data['passenger_count'])
            ->where('bags', '>=', $data['bag_count'])
            ->get();

        if ($fleets->isEmpty()) {
            throw ValidationException::withMessages([
                'capacity' => ['Sorry, we have no vehicles available that can accommodate your party size.'],
            ]);
        }

        $vehicles = $fleets->map(function (Fleet $fleet) use ($hours) {
            $bookingHours = $hours;

            if ($bookingHours < $fleet->minimum_hours) {
                $bookingHours = $fleet->minimum_hours;
            }

            $hourlyRate = $fleet->rate_per_hour * $bookingHours;
            $surcharges = 20;
            $totalPrice = $hourlyRate + $surcharges;

            return [
                'id' => $fleet->id,
                'name' => $fleet->name,
                'slug' => $fleet->slug,
                'seats' => $fleet->seats,
                'bags' => $fleet->bags,
                'thumbnail_url' => $fleet->thumbnail_url,
                'image_urls' => $fleet->image_urls,
                'price' => round($totalPrice, 2),
                'booking_duration' => "{$bookingHours} hours",
                'price_breakdown' => [
                    'base_fare' => round($hourlyRate, 2),
                    'surcharges' => round($surcharges, 2),
                    'hourly_rate' => round($fleet->rate_per_hour, 2),
                    'total_hours' => $bookingHours,
                    'total' => round($totalPrice, 2)
                ]
            ];
        });

        return HourlyBasedQuoteResource::collection($vehicles);
    }

    /**
     * Create a new booking with a 'pending_payment' status.
     *
     * @param  array  $data  Validated booking data.
     * @return Booking The newly created booking model instance.
     *
     * @throws ValidationException
     */
    public function createBooking(array $data): Booking
    {
        $quoteData = $this->getQuote($data);
        $selectedFleetQuote = $quoteData->firstWhere('id', $data['fleet_id']);

        if (! $selectedFleetQuote) {
            throw ValidationException::withMessages([
                'fleet_id' => 'The selected vehicle is not available for the specified requirements.',
            ]);
        }

        $finalPrice = $selectedFleetQuote['price'];

        $bookingData = array_merge($data, [
            'code' => 'BK-' . strtoupper(Str::random(9)),
            'price' => $finalPrice,
            'status' => 'pending_payment',
            'payment_status' => 'unpaid',
        ]);

        return Booking::create($bookingData);
    }

    /**
     * Create a Stripe Payment Intent for a booking.
     */
    public function createPaymentIntent(Booking $booking): PaymentIntent
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        return PaymentIntent::create([
            'amount' => $booking->price * 100,
            'currency' => 'usd',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => [
                'booking_id' => $booking->id,
                'booking_code' => $booking->code,
            ],
        ]);
    }

    /**
     * Helper to get simulated trip info based on service type.
     */
    protected function getSimulatedTripInfo(string $serviceType): array
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
            'distance_text' => $distanceMiles . ' miles'
        ];
    }
}
