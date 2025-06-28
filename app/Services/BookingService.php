<?php

namespace App\Services;

use App\Http\Resources\Booking\DistanceBasedQuoteResource;
use App\Http\Resources\Booking\HourlyBasedQuoteResource;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Fleet;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Log;
use Str;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class BookingService
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

        /** @var Collection<DistanceBasedQuoteResource> $vehicles */
        return $vehicles = $fleets->map(function (Fleet $fleet) use ($distanceInMiles, $data, $durationText, $durationInMinutes) {

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
                    'total' => round($totalPrice, 2),
                ],
                'distance' => [
                    'miles' => $distanceInMiles,
                    'minutes' => $durationInMinutes,
                    'description' => $durationText,
                ],
            ];
        });

        return DistanceBasedQuoteResource::collection(
            $vehicles->map(fn($v) => (object) $v)
        );
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

        /** @var Collection<HourlyBasedQuoteResource> $vehicles */
        return $vehicles = $fleets->map(function (Fleet $fleet) use ($hours) {
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
                    'total' => round($totalPrice, 2),
                ],
            ];
        });

        return HourlyBasedQuoteResource::collection(
            $vehicles->map(fn($v) => (object) $v)
        );
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
        $quoteObject = [
            'service_type' => $data['service_type'],
            'fleet_id' => $data['fleet_id'],
            'pickup_address' => $data['pickup']['address'] ?? null,
            'dropoff_address' => $data['dropoff']['address'] ?? null,
            'passenger_count' => $data['passengers'] ?? 1,
            'bag_count' => $data['bags'] ?? 0,
            'duration_hours' => $data['duration_hours'] ?? 1,
        ];
        $quoteData = $this->getQuote($quoteObject);
        $selectedFleetQuote = $quoteData->firstWhere('id', $data['fleet_id']);

        if (! $selectedFleetQuote) {
            throw ValidationException::withMessages([
                'fleet_id' => 'The selected vehicle is not available for the specified requirements.',
            ]);
        }

        $finalPrice = $selectedFleetQuote['price'];
        // Find or create customer by email
        $customerData = $data['customer'] ?? [];
        $customer = null;
        if (! empty($customerData['email'])) {
            $customer = Customer::firstOrNew(['email' => $customerData['email']]);
            $customer->first_name = $customerData['first_name'] ?? $customer->first_name;
            $customer->last_name = $customerData['last_name'] ?? $customer->last_name;
            $customer->phone = $customerData['phone'] ?? $customer->phone;
            $customer->language = $customerData['language'] ?? $customer->language;
            $customer->last_active = now();
            $customer->save();
            // Update bookings_count
            $customer->bookings_count = $customer->bookings()->count();
            $customer->save();
        }
        $bookingData = [
            'fleet_id' => $data['fleet_id'],
            'service_type' => $data['service_type'],
            'price' => $finalPrice,
            'status' => 'pending_payment',
            'payment_status' => 'unpaid',
            'customer_id' => $customer ? $customer->id : null,
            // Pickup details
            'pickup_datetime' => $data['pickup']['datetime'],
            'pickup_address' => $data['pickup']['address'],
            'pickup_latitude' => $data['pickup']['latitude'] ?? null,
            'pickup_longitude' => $data['pickup']['longitude'] ?? null,
            // Dropoff details (if provided)
            'dropoff_address' => $data['dropoff']['address'] ?? null,
            'dropoff_latitude' => $data['dropoff']['latitude'] ?? null,
            'dropoff_longitude' => $data['dropoff']['longitude'] ?? null,
            // Trip details
            'passenger_count' => $data['passengers'],
            'bag_count' => $data['bags'],
            'duration_hours' => $data['duration_hours'] ?? null,
            // Optional fields
            'is_accessible' => $data['accessible'] ?? false,
            'is_return_service' => $data['return_service'] ?? false,
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'] ?? null,
        ];

        $booking = Booking::create($bookingData);
        if ($customer) {
            $customer->bookings_count = $customer->bookings()->count();
            $customer->last_active = now();
            $customer->save();
        }

        return $booking;
    }

    /**
     * Create a Stripe Payment Intent for a booking.
     */
    public function createPaymentIntent(Booking $booking)
    {
        if ($booking->payment_method === 'paypal') {
            return $this->createPayPalOrder($booking);
        } else if ($booking->payment_method === 'stripe') {
            return $this->createStripePaymentIntent($booking);
        }
        throw new Exception('Invalid payment method: ' . $booking->payment_method);
    }

    /**
     * Create a PayPal Payment Intent for a booking.
     */
    public function createPayPalOrder(Booking $booking)
    {
        $paypalService = app(PayPalService::class);
        $paymentIntent = $paypalService->createPayment($booking->price, 'USD', [
            'booking_id' => $booking->id,
            'reference' => $booking->id,
            'description' => 'Booking Payment on ' . config('app.name'),
            'booking_code' => $booking->code,
        ]);

        return [
            'intent' => $paymentIntent['id'],
            'method' => 'paypal',
            'client_secret' => $paymentIntent['id'],
            'amount' => $booking->price,
            'currency' => 'USD',
        ];
    }

    /**
     * Create a Stripe Payment Intent for a booking.
     */
    public function createStripePaymentIntent(Booking $booking)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::create([
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

        return [
            'intent' => $paymentIntent->id,
            'method' => 'stripe',
            'client_secret' => $paymentIntent->client_secret,
            'amount' => $paymentIntent->amount / 100,
            'currency' => $paymentIntent->currency,
        ];
    }

    /**
     * Confirm payment and update booking status
     * This is used as a fallback when webhook fails
     */
    public function confirmPayment(string $bookingId, string $paymentIntentId): array
    {
        try {
            // Get the booking
            $booking = Booking::findOrFail($bookingId);
            if ($booking->payment_method === 'stripe') {
                return $this->confirmStripePayment($booking, $paymentIntentId);
            } else if ($booking->payment_method === 'paypal') {
                return $this->confirmPaypalPayment($booking, $paymentIntentId);
            }
            return [
                'success' => false,
                'message' => 'Invalid Booking payment',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to confirm payment: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to confirm payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Confirm a PayPal payment
     */
    public function confirmPaypalPayment($booking, $paymentIntentId)
    {
        $paypalService = app(PayPalService::class);
        $paymentIntent = $paypalService->captureOrder($paymentIntentId);

        // Check if payment was successful
        if ($paymentIntent['status'] !== 'COMPLETED') {
            return [
                'success' => false,
                'message' => 'Payment has not been completed yet.',
            ];
        }

        // Verify this payment intent belongs to this booking
        $code = $paymentIntent['purchase_units'][0]['custom_id'] ?? null;
        if ($code !== $booking->id) {
            return [
                'success' => false,
                'message' => 'Payment does not match this booking.',
            ];
        }

        // Update booking if not already confirmed
        if ($booking->status === 'pending_payment') {
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            // Create payment record
            Payment::create([
                'booking_id' => $booking->id,
                'payment_intent_id' => $paymentIntent['id'],
                'amount' => $paymentIntent['purchase_units'][0]['amount']['value'],
                'currency' => $paymentIntent['purchase_units'][0]['amount']['currency_code'],
                'customer_name' => $booking->customer?->first_name . ' ' . $booking->customer?->last_name,
                'customer_email' => $booking->customer?->email,
                'status' => 'completed',
                'payment_method' => 'paypal',
                'gateway_name' => 'paypal',
                'gateway_ref' => $paymentIntent['purchase_units'][0]['reference_id'],
                'gateway_payload' => $paymentIntent,
            ]);
        }

        return [
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'code' => $booking->code,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
            ],
        ];
    }
    public function confirmStripePayment($booking, $paymentIntentId)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Verify payment intent with Stripe
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

        // Check if payment was successful
        if ($paymentIntent->status !== 'succeeded') {
            return [
                'success' => false,
                'message' => 'Payment has not been completed yet.',
            ];
        }

        // Verify this payment intent belongs to this booking
        if ($paymentIntent->metadata->booking_id !== $booking->id) {
            return [
                'success' => false,
                'message' => 'Payment does not match this booking.',
            ];
        }

        // Update booking if not already confirmed
        if ($booking->status === 'pending_payment') {
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            // Create payment record
            Payment::create([
                'booking_id' => $booking->id,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'customer_name' => $booking->customer?->first_name . ' ' . $booking->customer?->last_name,
                'customer_email' => $booking->customer?->email,
                'status' => 'completed',
                'payment_method' => 'stripe',
                'gateway_name' => 'stripe',
                'gateway_ref' => $paymentIntent->payment_method,
                'gateway_payload' => $paymentIntent,
            ]);
        }

        return [
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'code' => $booking->code,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
            ],
        ];
    }

    /**
     * Get booking details by ID or Code
     */
    public function getBookingDetails($id)
    {
        if (Str::isUuid($id)) {
            $booking = Booking::with(['fleet', 'latestPayment'])->findOrFail($id);
        } else {
            $booking = Booking::with(['fleet', 'latestPayment'])->where('code', $id)->firstOrFail();
        }

        return $booking;
    }

    /**
     * Process webhook payment confirmation
     * This is called from the webhook handler
     */
    public function processWebhookPayment($paymentIntent): bool
    {
        try {
            $bookingId = $paymentIntent->metadata->booking_id ?? null;

            if (! $bookingId) {
                \Log::warning('Webhook payment intent missing booking_id', [
                    'payment_intent_id' => $paymentIntent->id,
                ]);

                return false;
            }

            $booking = Booking::find($bookingId);
            if (! $booking) {
                \Log::warning('Booking not found for webhook', [
                    'booking_id' => $bookingId,
                    'payment_intent_id' => $paymentIntent->id,
                ]);

                return false;
            }

            // Update booking status
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            // Create payment record
            Payment::updateOrCreate(
                ['payment_intent_id' => $paymentIntent->id],
                [
                    'booking_id' => $booking->id,
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => $paymentIntent->currency,
                    'customer_name' => $booking->customer?->first_name . ' ' . $booking->customer?->last_name,
                    'customer_email' => $booking->customer?->email,
                    'status' => 'completed',
                    'payment_method' => 'stripe',
                    'gateway_name' => 'stripe',
                    'gateway_ref' => $paymentIntent->payment_method,
                    'gateway_payload' => $paymentIntent,
                ]
            );

            \Log::info('Booking payment confirmed via webhook', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->code,
                'payment_intent_id' => $paymentIntent->id,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Webhook payment processing failed', [
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
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
            'distance_text' => $distanceMiles . ' miles',
        ];
    }

    /**
     * Update a booking with validated data.
     * Handles mapping of nested request arrays to flat DB columns.
     */
    public function updateBooking(array $data, Booking $booking): Booking
    {
        $update = [];

        // Customer Details
        if (isset($data['customer']) && ! empty($data['customer']['email'])) {
            $customerData = $data['customer'];
            $currentCustomer = $booking->customer;
            if ($currentCustomer && $currentCustomer->email === $customerData['email']) {
                // Update existing customer fields
                if (isset($customerData['first_name'])) {
                    $currentCustomer->first_name = $customerData['first_name'];
                }
                if (isset($customerData['last_name'])) {
                    $currentCustomer->last_name = $customerData['last_name'];
                }
                if (isset($customerData['phone'])) {
                    $currentCustomer->phone = $customerData['phone'];
                }
                if (isset($customerData['language'])) {
                    $currentCustomer->language = $customerData['language'];
                }
                $currentCustomer->last_active = now();
                $currentCustomer->save();
            } else {
                // Find or create new customer by email
                $customer = Customer::firstOrNew(['email' => $customerData['email']]);
                $customer->first_name = $customerData['first_name'] ?? $customer->first_name;
                $customer->last_name = $customerData['last_name'] ?? $customer->last_name;
                $customer->phone = $customerData['phone'] ?? $customer->phone;
                $customer->language = $customerData['language'] ?? $customer->language;
                $customer->last_active = now();
                $customer->save();
                $update['customer_id'] = $customer->id;
            }
        }

        // Booking Choices
        if (isset($data['service_type'])) {
            $update['service_type'] = $data['service_type'];
        }
        if (isset($data['fleet_id'])) {
            $update['fleet_id'] = $data['fleet_id'];
        }

        // Trip Details
        if (isset($data['pickup'])) {
            $pickup = $data['pickup'];
            if (isset($pickup['datetime'])) {
                $update['pickup_datetime'] = $pickup['datetime'];
            }
            if (isset($pickup['address'])) {
                $update['pickup_address'] = $pickup['address'];
            }
            if (array_key_exists('latitude', $pickup)) {
                $update['pickup_latitude'] = $pickup['latitude'];
            }
            if (array_key_exists('longitude', $pickup)) {
                $update['pickup_longitude'] = $pickup['longitude'];
            }
        }
        if (isset($data['dropoff'])) {
            $dropoff = $data['dropoff'];
            if (isset($dropoff['address'])) {
                $update['dropoff_address'] = $dropoff['address'];
            }
            if (array_key_exists('latitude', $dropoff)) {
                $update['dropoff_latitude'] = $dropoff['latitude'];
            }
            if (array_key_exists('longitude', $dropoff)) {
                $update['dropoff_longitude'] = $dropoff['longitude'];
            }
        }
        if (isset($data['passengers'])) {
            $update['passenger_count'] = $data['passengers'];
        }
        if (isset($data['bags'])) {
            $update['bag_count'] = $data['bags'];
        }
        if (isset($data['accessible'])) {
            $update['is_accessible'] = $data['accessible'];
        }
        if (isset($data['return_service'])) {
            $update['is_return_service'] = $data['return_service'];
        }
        if (isset($data['duration_hours'])) {
            $update['duration_hours'] = $data['duration_hours'];
        }

        // Pricing
        if (isset($data['price'])) {
            $update['price'] = $data['price'];
        }
        if (isset($data['payment'])) {
            $payment = $data['payment'];
            if (isset($payment['method'])) {
                $update['payment_method'] = $payment['method'];
            }
        }
        // Additional Info
        if (array_key_exists('notes', $data)) {
            $update['notes'] = $data['notes'];
        }

        // Actually update the booking
        $booking->update($update);

        return $booking->fresh();
    }
}
