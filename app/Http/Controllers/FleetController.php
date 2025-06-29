<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFleetRequest;
use App\Http\Requests\UpdateFleetRequest;
use App\Http\Resources\FleetResource;
use App\Models\Fleet;
use App\Services\FleetService;
use App\Traits\ApiResponse;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FleetController extends Controller
{
    use ApiResponse;

    public function __construct(protected FleetService $fleetService) {}

    /**
     * Fetch all fleets
     *
     * @unauthenticated
     */
    public function index(Request $request)
    {
        $fleets = Cache::remember('all_active_fleets', now()->addMinutes(20), function () {
            return Fleet::active()->orderBy('order', 'asc')->get();
        });

        return $this->dataResponse('All Fleets', FleetResource::collection($fleets));
    }

    /**
     * Fetch a fleet by slug or id
     *
     * @unauthenticated
     */
    public function show($slug)
    {
        $fleet = cache()->remember("fleet:slug:{$slug}", now()->addHour(), function () use ($slug) {
            if (Str::isUuid($slug)) {
                return Fleet::where('id', $slug)->firstOrFail();
            } else {
                return Fleet::where('slug', $slug)->firstOrFail();
            }
        });

        return $this->dataResponse('Fleet Details', FleetResource::make($fleet));
    }

    /**
     * Fetch all fleets (Admin)
     */
    public function adminIndex(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $fleets = Fleet::orderBy('order', 'asc')->paginate($perPage);

        return $this->paginatedResponse('All Fleets', FleetResource::collection($fleets), $fleets);
    }

    /**
     * Vehicle Details
     */
    public function adminShow(Fleet $fleet)
    {
        return $this->dataResponse('Fleet Details', FleetResource::make($fleet));
    }

    /**
     * Add new fleet
     */
    public function store(StoreFleetRequest $request)
    {
        $validated = $request->validated();
        $fleet = $this->fleetService->create($validated, $request);

        return $this->dataResponse('Fleet created successfully', FleetResource::make($fleet), 201);
    }

    /**
     * Update fleet
     */
    public function update(UpdateFleetRequest $request, Fleet $fleet)
    {
        $validated = $request->validated();
        $fleet = $this->fleetService->update($fleet, $validated, $request);

        cache()->forget("fleet:slug:{$fleet->slug}");
        cache()->forget("fleet:slug:{$fleet->id}");
        return $this->dataResponse('Fleet updated successfully', FleetResource::make($fleet));
    }

    /**
     * Remove the specified fleet
     */
    public function destroy(Fleet $fleet)
    {
        $this->fleetService->delete($fleet);

        return $this->successResponse('Fleet deleted successfully');
    }

    /**
     * Reorder fleets
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'fleets' => 'required|array',
            'fleets.*.id' => 'required|exists:fleets,id',
            'fleets.*.order' => 'required|integer|min:1',
        ]);

        foreach ($request->fleets as $fleetData) {
            Fleet::where('id', $fleetData['id'])->update(['order' => $fleetData['order']]);
        }

        cache()->forget('all_active_fleets');

        return $this->successResponse('Fleet order updated successfully');
    }

    /**
     * Toggle fleet active status
     */
    public function toggleStatus(Fleet $fleet)
    {
        $fleet->update(['is_active' => ! $fleet->is_active]);

        $status = $fleet->is_active ? 'activated' : 'deactivated';

        cache()->forget('all_active_fleets');

        return $this->dataResponse("Fleet {$status} successfully", FleetResource::make($fleet));
    }
}
