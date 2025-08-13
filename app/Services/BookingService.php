<?php

namespace App\Services;

use App\Events\BookingConfirmed;
use App\Http\Resources\Booking\DistanceBasedQuoteResource;
use App\Http\Resources\Booking\HourlyBasedQuoteResource;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Fleet;
use App\Services\Pricing\PricingCalculator;
use App\Services\Route\RouteService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Str;

class BookingService
{
    private const DISTANCE_SERVICES = ['point_to_point', 'airport_pickup', 'airport_transfer', 'round_trip'];

    private const HOURLY_SERVICES = ['wedding', 'event', 'other'];

    public function __construct(
        private PricingCalculator $pricingCalculator,
        private RouteService $routeService,
        private CustomerService $customerService
    ) {}

    /**
     * Get a quote for a trip based on service type and other parameters.
     */
    public function getQuote(array $data)
    {
        $serviceType = $data['service_type'] ?? 'point_to_point';

        if (in_array($serviceType, self::DISTANCE_SERVICES)) {
            return $this->getDistanceBasedQuote($data);
        }

        if (in_array($serviceType, self::HOURLY_SERVICES)) {
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
        $routeInfo = $this->routeService->getRouteInfo($data);
        $availableFleets = $this->getAvailableFleets($data['passenger_count'], $data['bag_count']);

        return $vehicles = $availableFleets->map(function (Fleet $fleet) use ($routeInfo, $data) {
            $pricing = $this->pricingCalculator->calculateDistanceBasedPrice(
                $fleet,
                $routeInfo['distance_miles'],
                $data['service_type']
            );

            return $this->formatDistanceQuoteData($fleet, $pricing, $routeInfo);
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
        $this->validateHourlyData($data);

        $availableFleets = $this->getAvailableFleets($data['passenger_count'], $data['bag_count']);
        $requestedHours = (int) $data['duration_hours'];

        return $vehicles = $availableFleets->map(function (Fleet $fleet) use ($requestedHours) {
            $pricing = $this->pricingCalculator->calculateHourlyPrice($fleet, $requestedHours);

            return $this->formatHourlyQuoteData($fleet, $pricing);
        });

        return HourlyBasedQuoteResource::collection(
            $vehicles->map(fn($v) => (object) $v)
        );
    }

    /**
     * Create a new booking with a 'pending_payment' status.
     */
    public function createBooking(array $data): Booking
    {
        $selectedFleetQuote = $this->validateFleetSelection($data);
        $customer = $this->customerService->findOrCreateCustomer($data['customer'] ?? []);

        $bookingData = $this->buildBookingData($data, $selectedFleetQuote['price'], $customer);
        $booking = Booking::create($bookingData);

        if ($customer) {
            $this->customerService->updateCustomerStats($customer);
        }
        // notify admin of manual payment
        if ($data['payment_method'] === 'cash') {
            event(new BookingConfirmed($booking));
        }
        return $booking;
    }

    /**
     * Get booking details by ID or Code
     */
    public function getBookingDetails($id): Booking
    {
        $query = Booking::with(['fleet', 'latestPayment', 'payments']);

        return Str::isUuid($id)
            ? $query->findOrFail($id)
            : $query->where('code', $id)->firstOrFail();
    }

    /**
     * Update a booking with validated data.
     */
    public function updateBooking(array $data, Booking $booking): Booking
    {
        $updateData = $this->buildUpdateData($data, $booking);
        if (! empty($updateData)) {
            $booking->update($updateData);
        }

        return $booking->fresh();
    }

    /**
     * Get available fleets based on capacity requirements
     */
    private function getAvailableFleets(int $passengerCount, int $bagCount): Collection
    {
        $fleets = Fleet::active()
            ->where('seats', '>=', $passengerCount)
            ->where('bags', '>=', $bagCount)
            ->get();

        if ($fleets->isEmpty()) {
            throw ValidationException::withMessages([
                'capacity' => ['Sorry, we have no vehicles available that can accommodate your party size.'],
            ]);
        }

        return $fleets;
    }

    /**
     * Validate fleet selection against quote
     */
    private function validateFleetSelection(array $data): array
    {
        $quoteData = $this->getQuote($this->buildQuoteObject($data));
        $selectedFleetQuote = $quoteData->firstWhere('id', $data['fleet_id']);

        if (! $selectedFleetQuote) {
            throw ValidationException::withMessages([
                'fleet_id' => 'The selected vehicle is not available for the specified requirements.',
            ]);
        }

        return $selectedFleetQuote;
    }

    /**
     * Build quote object from booking data
     */
    private function buildQuoteObject(array $data): array
    {
        return [
            'service_type' => $data['service_type'],
            'fleet_id' => $data['fleet_id'],
            'pickup_address' => $data['pickup']['address'] ?? null,
            'dropoff_address' => $data['dropoff']['address'] ?? null,
            'passenger_count' => $data['passengers'] ?? 1,
            'bag_count' => $data['bags'] ?? 0,
            'duration_hours' => $data['duration_hours'] ?? 1,
        ];
    }

    /**
     * Build booking data array
     */
    private function buildBookingData(array $data, float $price, ?Customer $customer): array
    {

        $isCashPayment = ($data['payment_method'] ?? '') === 'cash';
        $bookingStatus = $isCashPayment ? 'confirmed' : 'pending_payment';
        return [
            'fleet_id' => $data['fleet_id'],
            'service_type' => $data['service_type'],
            'price' => $price,
            'status' => $bookingStatus,
            'payment_status' => 'unpaid',
            'customer_id' => $customer?->id,
            'pickup_datetime' => $data['pickup']['datetime'],
            'pickup_address' => $data['pickup']['address'],
            'pickup_latitude' => $data['pickup']['latitude'] ?? null,
            'pickup_longitude' => $data['pickup']['longitude'] ?? null,
            'dropoff_address' => $data['dropoff']['address'] ?? null,
            'dropoff_latitude' => $data['dropoff']['latitude'] ?? null,
            'dropoff_longitude' => $data['dropoff']['longitude'] ?? null,
            'passenger_count' => $data['passengers'],
            'bag_count' => $data['bags'],
            'duration_hours' => $data['duration_hours'] ?? null,
            'is_accessible' => $data['accessible'] ?? false,
            'is_return_service' => $data['return_service'] ?? false,
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'] ?? null,
        ];
    }

    /**
     * Build update data from request
     */
    private function buildUpdateData(array $data, Booking $booking): array
    {
        $updateData = [];

        // Handle customer updates
        if (isset($data['customer'])) {
            $customer = $this->customerService->handleCustomerUpdate($data['customer'], $booking);
            if ($customer) {
                $updateData['customer_id'] = $customer->id;
            }
        }

        // Map simple fields
        $fieldMappings = [
            'service_type' => 'service_type',
            'fleet_id' => 'fleet_id',
            'passengers' => 'passenger_count',
            'bags' => 'bag_count',
            'accessible' => 'is_accessible',
            'return_service' => 'is_return_service',
            'duration_hours' => 'duration_hours',
            'price' => 'price',
            'notes' => 'notes',
        ];

        foreach ($fieldMappings as $requestKey => $dbColumn) {
            if (array_key_exists($requestKey, $data)) {
                $updateData[$dbColumn] = $data[$requestKey];
            }
        }

        // Handle nested arrays
        $updateData = array_merge($updateData, $this->mapNestedFields($data));

        return $updateData;
    }

    /**
     * Map nested fields (pickup, dropoff, payment)
     */
    private function mapNestedFields(array $data): array
    {
        $mapped = [];

        // Pickup fields
        if (isset($data['pickup'])) {
            $pickupMappings = [
                'datetime' => 'pickup_datetime',
                'address' => 'pickup_address',
                'latitude' => 'pickup_latitude',
                'longitude' => 'pickup_longitude',
            ];

            foreach ($pickupMappings as $key => $column) {
                if (array_key_exists($key, $data['pickup'])) {
                    $mapped[$column] = $data['pickup'][$key];
                }
            }
        }

        // Dropoff fields
        if (isset($data['dropoff'])) {
            $dropoffMappings = [
                'address' => 'dropoff_address',
                'latitude' => 'dropoff_latitude',
                'longitude' => 'dropoff_longitude',
            ];

            foreach ($dropoffMappings as $key => $column) {
                if (array_key_exists($key, $data['dropoff'])) {
                    $mapped[$column] = $data['dropoff'][$key];
                }
            }
        }

        // Payment method
        if (isset($data['payment_method'])) {
            $mapped['payment_method'] = $data['payment_method'];
        }

        return $mapped;
    }

    /**
     * Format distance-based quote data
     */
    private function formatDistanceQuoteData(Fleet $fleet, array $pricing, array $routeInfo): array
    {
        return [
            'id' => $fleet->id,
            'name' => $fleet->name,
            'slug' => $fleet->slug,
            'seats' => $fleet->seats,
            'bags' => $fleet->bags,
            'thumbnail_url' => $fleet->thumbnail_url,
            'image_urls' => $fleet->image_urls,
            'price' => $pricing['total'],
            'estimated_duration' => $routeInfo['duration_text'],
            'price_breakdown' => $pricing['breakdown'],
            'distance' => [
                'miles' => $routeInfo['distance_miles'],
                'minutes' => round($routeInfo['duration_seconds'] / 60),
                'description' => $routeInfo['duration_text'],
            ],
        ];
    }

    /**
     * Format hourly-based quote data
     */
    private function formatHourlyQuoteData(Fleet $fleet, array $pricing): array
    {
        return [
            'id' => $fleet->id,
            'name' => $fleet->name,
            'slug' => $fleet->slug,
            'seats' => $fleet->seats,
            'bags' => $fleet->bags,
            'thumbnail_url' => $fleet->thumbnail_url,
            'image_urls' => $fleet->image_urls,
            'price' => $pricing['total'],
            'booking_duration' => "{$pricing['hours']} hours",
            'price_breakdown' => $pricing['breakdown'],
        ];
    }

    /**
     * Validate hourly service data
     */
    private function validateHourlyData(array $data): void
    {
        if (empty($data['duration_hours'])) {
            throw ValidationException::withMessages([
                'duration_hours' => ['Booking duration is required for this service type.'],
            ]);
        }
    }
}
