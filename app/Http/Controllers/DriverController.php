<?php

namespace App\Http\Controllers;

use App\Actions\DriverAction;
use App\Http\Requests\Driver\StoreDriverRequest;
use App\Http\Requests\Driver\UpdateDriverRequest;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    use ApiResponse;

    public function __construct(protected DriverAction $driverAction) {}

    /**
     *All Drivers
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'string',
            'status' => 'string|in:active,inactive,on_leave,suspended',
            'available' => 'boolean',
        ]);
        $perPage = $request->input('per_page', 20);
        $search = $request->input('search');

        $query = Driver::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('language', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->available !== null) {
            $query->where('is_available', filter_var($request->available, FILTER_VALIDATE_BOOLEAN));
        }

        $drivers = $query->latest()->paginate($perPage);

        return $this->paginatedResponse(
            'Drivers retrieved successfully',
            DriverResource::collection($drivers),
            $drivers
        );
    }

    /**
     * Add new driver.
     */
    public function store(StoreDriverRequest $request): JsonResponse
    {
        $driver = $this->driverAction->create($request->validated());

        return $this->successResponse(
            'Driver created successfully',
            new DriverResource($driver),
            201
        );
    }

    /**
     * Show Driver
     */
    public function show(Driver $driver): JsonResponse
    {
        return $this->successResponse(
            'Driver retrieved successfully',
            new DriverResource($driver)
        );
    }

    /**
     * Update driver.
     */
    public function update(UpdateDriverRequest $request, Driver $driver): JsonResponse
    {
        $driver = $this->driverAction->update($driver, $request->validated());

        return $this->successResponse(
            'Driver updated successfully',
            new DriverResource($driver)
        );
    }

    /**
     * Delete driver.
     */
    public function destroy(Driver $driver): JsonResponse
    {
        $this->driverAction->delete($driver);

        return $this->successResponse('Driver deleted successfully');
    }
}
