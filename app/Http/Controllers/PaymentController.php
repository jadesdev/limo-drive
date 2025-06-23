<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    /**
     * All Payments.
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'string|in:pending_payment,paid,failed,cancelled',
            'search' => 'string',
        ]);

        $perPage = $request->input('per_page', 20);
        $query = Payment::query();

        $query->with('booking');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('booking', function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $payments = $query->latest()->paginate($perPage);

        return $this->paginatedResponse(
            'Payments retrieved successfully',
            PaymentResource::collection($payments),
            $payments
        );
    }

    /**
     * Get a specific payment.
     */
    public function show(Payment $payment)
    {
        $payment->load('booking');
        return $this->successResponse(
            'Payment retrieved successfully',
            new PaymentResource($payment)
        );
    }
}
