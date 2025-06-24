<?php

namespace App\Actions;

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
            if (!$routeInfo) {
                throw ValidationException::withMessages([
                    'route' => ['We could not calculate the route at this time. Please try again later.'],
                ]);
            }
        }

        $distanceInKm = $routeInfo['distance_meters'] / 1000;
        $durationInMinutes = $routeInfo['duration_seconds'] / 60;

        $fleets = Fleet::active()
            ->where('seats', '>=', $data['passenger_count'])
            ->where('bags', '>=', $data['bag_count'])
            ->get();

        if ($fleets->isEmpty()) {
            throw ValidationException::withMessages([
                'capacity' => ['Sorry, we have no vehicles available that can accommodate your party size.'],
            ]);
        }

        return $fleets->map(function (Fleet $fleet) use ($distanceInKm, $durationInMinutes, $data) {
            $price = $fleet->base_rate +
                ($fleet->rate_per_km * $distanceInKm) +
                ($fleet->rate_per_minute * $durationInMinutes);

            if ($data['service_type'] === 'round_trip') {
                $price *= 2;
            }

            return [
                'id' => $fleet->id,
                'name' => $fleet->name,
                'seats' => $fleet->seats,
                'bags' => $fleet->bags,
                'thumbnail_url' => $fleet->thumbnail_url,
                'price' => round($price, 2),
                'estimated_duration' => $routeInfo['duration_text'] ?? null,
            ];
        });
    }

    /**
     * Calculate quotes for hourly-based services.
     */
    protected function getHourlyQuote(array $data): Collection
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

        return $fleets->map(function (Fleet $fleet) use ($hours) {
            $bookingHours = $hours;

            if ($bookingHours < $fleet->minimum_hours) {
                $bookingHours = $fleet->minimum_hours;
            }

            $price = ($fleet->rate_per_minute * 60) * $bookingHours;

            return [
                'id' => $fleet->id,
                'name' => $fleet->name,
                'seats' => $fleet->seats,
                'bags' => $fleet->bags,
                'thumbnail_url' => $fleet->thumbnail_url,
                'price' => round($price, 2),
                'booking_duration' => "{$bookingHours} hours",
            ];
        });
    }


    /**
     * Create a new booking with a 'pending_payment' status.
     *
     * @param  array  $data Validated booking data.
     * @return Booking The newly created booking model instance.
     *
     * @throws ValidationException
     */
    public function createBooking(array $data): Booking
    {
        $quoteData = $this->getQuote($data);
        $selectedFleetQuote = $quoteData->firstWhere('id', $data['fleet_id']);

        if (!$selectedFleetQuote) {
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
     *
     * @param  Booking  $booking
     * @return PaymentIntent
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
        switch ($serviceType) {
            case 'point_to_point':
                return ['distance_meters' => 15000, 'duration_seconds' => 1800, 'duration_text' => '30 mins'];
            case 'airport_pickup':
            case 'airport_transfer':
                return ['distance_meters' => 40000, 'duration_seconds' => 3600, 'duration_text' => '1 hour'];
            case 'round_trip':
                return ['distance_meters' => 20000, 'duration_seconds' => 2400, 'duration_text' => '40 mins (each way)'];
            default:
                return ['distance_meters' => 10000, 'duration_seconds' => 1200, 'duration_text' => '20 mins'];
        }
    }
}
