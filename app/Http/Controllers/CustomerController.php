<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    use ApiResponse;

    /**
     * List all customers
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);
        $perPage = $request->input('per_page', 20);
        $query = Customer::query();
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($request->filled('start_date')) {
            $query->where('last_active', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->where('last_active', '<=', $request->input('end_date'));
        }
        $customers = $query->latest('last_active')->paginate($perPage);

        return $this->paginatedResponse(
            'Customers retrieved successfully.',
            CustomerResource::collection($customers),
            $customers
        );
    }

    /**
     * Customer details
     */
    public function show(Customer $customer)
    {
        return $this->dataResponse(
            'Customer retrieved successfully.',
            CustomerResource::make($customer)
        );
    }

    /**
     * Update a customer
     */
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email:rfc,dns', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)],
            'phone' => ['sometimes', 'string', 'max:30'],
            'language' => ['sometimes', 'string', 'max:30'],
            'last_active' => ['sometimes', 'date'],
        ]);
        $customer->update($data);

        return $this->dataResponse(
            'Customer updated successfully.',
            CustomerResource::make($customer)
        );
    }

    /**
     * Delete a customer
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return $this->successResponse('Customer deleted successfully.');
    }
}
