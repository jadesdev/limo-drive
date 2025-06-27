<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\Booking\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    use ApiResponse;

    public function __construct(protected BookingService $bookingService) {}

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

        $bookings = $query->with(['fleet', 'driver', 'payments', 'latestPayment'])->latest()->paginate($perPage);

        return $this->paginatedResponse(
            'Bookings retrieved successfully.',
            BookingResource::collection($bookings),
            $bookings,
        );
    }

    /**
     * Booking Calendar
     */
    public function calendar(Request $request)
    {
        $validated = $request->validate([
            /** @example 2025-06-27 */
            'start_date' => 'required|date_format:Y-m-d',
            /** @example 2025-06-27 */
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'status' => 'nullable|string|in:in_progress,confirmed,completed,rejected',
        ]);
        $query = Booking::query()
            ->with(['customer:id,first_name,last_name'])
            ->whereBetween('pickup_datetime', [$validated['start_date'], $validated['end_date']]);

        if (! empty($validated['status'])) {
            $dbStatus = match ($validated['status']) {
                'in_progress' => 'pending_payment',
                'rejected' => 'cancelled',
                default => $validated['status'],
            };

            if ($validated['status'] === 'in_progress') {
                $query->whereIn('status', ['pending_payment', 'confirmed']);
            } else {
                $query->where('status', $dbStatus);
            }
        }

        $bookings = $query->get();

        $calendarEvents = $bookings->map(function (Booking $booking) {
            $durationHours = $booking->duration_hours ?? 1;
            $endTime = $booking->pickup_datetime->copy()->addHours($durationHours);

            $color = match ($booking->status) {
                'pending_payment' => '#6c757d',
                'confirmed' => '#28a745',
                'completed' => '#0d6efd',
                'cancelled' => '#dc3545',
                default => '#ffc107',
            };

            return [
                'id' => $booking->id,
                'title' => trim(($booking->customer->first_name ?? '') . ' ' . ($booking->customer->last_name ?? 'Guest')),
                'start' => $booking->pickup_datetime->toIso8601String(),
                'end' => $endTime->toIso8601String(),
                'color' => $color,
                'meta' => [
                    'booking_code' => $booking->code,
                    'status' => $booking->status,
                    'location' => $booking->pickup_address,
                ],
            ];
        });

        return $this->dataResponse(
            'Bookings Calendar.',
            $calendarEvents
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
