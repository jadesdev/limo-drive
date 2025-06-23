<?php

namespace App\Http\Controllers;

use App\Actions\ServiceAction;
use App\Http\Requests\ServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Traits\ApiResponse;
use App\Traits\ServiceTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    use ApiResponse;
    use ServiceTrait;

    public function __construct(protected ServiceAction $serviceAction) {}

    /**
     * Fetch all services
     *
     * @unauthenticated
     */
    public function index(Request $request)
    {
        $services = Service::active()->orderBy('order', 'asc')->get();

        return $this->dataResponse('All Services', ServiceResource::collection($services));
    }

    /**
     * Fetch a service by slug or id
     *
     * @unauthenticated
     */
    public function show($slug)
    {
        if (Str::isUuid($slug)) {
            $service = Service::where('id', $slug)->firstOrFail();
        } else {
            $service = Service::where('slug', $slug)->firstOrFail();
        }

        return $this->dataResponse('Service Details', ServiceResource::make($service));
    }

    /**
     * Fetch all services (Admin)
     */
    public function adminIndex(Request $request)
    {
        $search = $request->input('search', '');

        $query = Service::orderBy('order', 'asc');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
        }

        $services = $query->get();

        return $this->dataResponse('All Services', ServiceResource::collection($services));
    }

    /**
     * Service Deetails (Admin)
     */
    public function adminShow(Service $service)
    {
        return $this->dataResponse('Service Details', ServiceResource::make($service));
    }

    /**
     * Store a new service (Admin)
     */
    public function store(ServiceRequest $request)
    {
        $validated = $request->validated();
        $service = $this->serviceAction->create($validated);
        return $this->dataResponse('Service created successfully', ServiceResource::make($service));
    }

    /**
     * Update service (Admin)
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        $validated = $request->validated();
        $service = $this->serviceAction->update($service, $validated);
        return $this->dataResponse('Service updated successfully', ServiceResource::make($service));
    }

    /**
     * Delete service (Admin)
     */
    public function destroy(Service $service)
    {
        $this->deleteServiceImages($service);

        $service->delete();

        return $this->successResponse('Service deleted successfully');
    }
}
