<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\GetQuoteRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\Booking\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(protected BookingService $bookingService) {}

    /**
     * Get a price quote booking.
     *
     * @unauthenticated
     */
    public function getQuote(GetQuoteRequest $request): JsonResponse
    {
        $quoteData = $this->bookingService->getQuote($request->validated());

        return $this->dataResponse('Quote retrieved successfully.', $quoteData);
    }

    /**
     * Store a new booking.
     *
     * @unauthenticated
     */
    public function store(CreateBookingRequest $request)
    {
        $booking = $this->bookingService->createBooking($request->validated());
        $paymentIntent = $this->bookingService->createPaymentIntent($booking);

        return $this->dataResponse(
            'Booking created successfully. Please proceed to payment.',
            [
                'booking' => [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->code,
                    'price' => $booking->price,
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                ],
                'payment' => $paymentIntent,
            ],
            201
        );
    }

    /**
     * Create a payment intent for a given booking.
     *
     * @unauthenticated
     */
    public function createPaymentIntent(Booking $booking): JsonResponse
    {
        if ($booking->payment_status === 'paid') {
            return $this->errorResponse('This booking has already been paid for.', 409);
        }

        $paymentIntent = $this->bookingService->createPaymentIntent($booking);

        return $this->dataResponse('Payment intent created successfully.', $paymentIntent);
    }

    /**
     * Confirm payment completion
     *
     * This handles cases where webhook fails or needs manual confirmation
     *
     * @unauthenticated
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|uuid|exists:bookings,id',
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $result = $this->bookingService->confirmPayment(
                $validated['booking_id'],
                $validated['payment_intent_id']
            );

            if ($result['success']) {
                return $this->dataResponse(
                    'Payment confirmed successfully.',
                    [
                        'booking' => $result['booking'],
                        'payment_status' => 'paid',
                    ]
                );
            } else {
                return $this->errorResponse(
                    $result['message'] ?? 'Payment confirmation failed.',
                    400
                );
            }
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Payment confirmation error: ' . $e->getMessage(),
                422
            );
        }
    }

    /**
     * Get booking details by ID or Code
     *
     * @unauthenticated
     */
    public function show(string $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingDetails($id);

        return $this->dataResponse(
            'Booking retrieved successfully.',
            $booking
        );
    }

    /**
     * Admin booking History
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|string|in:pending_payment,paid,cancelled',
            'driver_id' => 'nullable|uuid|exists:drivers,id',
            'payment_status' => 'nullable|string|in:pending_payment,paid,cancelled',
        ]);
        $perPage = $request->input('per_page', 20);
        $query = Booking::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->has('payment_status')) {
            $query->whereHas('payment', function ($query) use ($request) {
                $query->where('status', $request->payment_status);
            });
        }

        $bookings = $query->with(['fleet', 'driver', 'payments', 'latestPayment'])->paginate($perPage);

        return $this->paginatedResponse(
            'Bookings retrieved successfully.',
            BookingResource::collection($bookings),
            $bookings,
        );
    }

    /**
     * Booking details (Admin)
     *
     * Get booking details by ID or Code
     */
    public function adminShow(string $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingDetails($id);

        return $this->dataResponse(
            'Booking retrieved successfully.',
            $booking
        );
    }

    /**
     * Assign Driver to Booking
     */
    public function assignDriver(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'driver_id' => 'required|uuid|exists:drivers,id',
        ]);

        $booking->update([
            'driver_id' => $validated['driver_id'],
        ]);

        return $this->dataResponse(
            'Driver assigned successfully.',
            $booking
        );
    }

    /**
     * Update Booking
     */
    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        $validated = $request->validated();

        $updatedBooking = $this->bookingService->updateBooking($validated, $booking);

        return $this->dataResponse(
            'Booking updated successfully.',
            $updatedBooking
        );
    }
}
