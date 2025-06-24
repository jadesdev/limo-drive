<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFleetRequest;
use App\Http\Requests\UpdateFleetRequest;
use App\Http\Resources\FleetResource;
use App\Models\Fleet;
use App\Services\FileUploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FleetController extends Controller
{
    use ApiResponse;

    public function __construct(private FileUploadService $fileUploadService) {}

    /**
     * Fetch all fleets
     *
     * @unauthenticated
     */
    public function index(Request $request)
    {
        $fleets = Fleet::active()->orderBy('order', 'asc')->get();

        return $this->dataResponse('All Fleets', FleetResource::collection($fleets));
    }

    /**
     * Fetch a fleet by slug or id
     *
     * @unauthenticated
     */
    public function show($slug)
    {
        if (Str::isUuid($slug)) {
            $fleet = Fleet::where('id', $slug)->firstOrFail();
        } else {
            $fleet = Fleet::where('slug', $slug)->firstOrFail();
        }

        return $this->dataResponse('Fleet Details', FleetResource::make($fleet));
    }

    /**
     * Fetch all fleets (Admin)
     */
    public function adminIndex(Request $request)
    {
        $fleets = Fleet::orderBy('order', 'asc')->get();

        return $this->dataResponse('All Fleets', FleetResource::collection($fleets));
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

        // Generate slug from name if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->uploadFile($request->file('thumbnail'), 'fleets');
        }

        // Handle multiple images upload
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $this->uploadFile($image, 'fleets');
            }
            $validated['images'] = $imagePaths;
        }

        // Set order if not provided (next highest order)
        if (empty($validated['order'])) {
            $validated['order'] = Fleet::max('order') + 1;
        }

        $fleet = Fleet::create($validated);

        return $this->dataResponse('Fleet created successfully', FleetResource::make($fleet), 201);
    }

    /**
     * Update fleet
     */
    public function update(UpdateFleetRequest $request, Fleet $fleet)
    {
        $validated = $request->validated();

        // Update slug if name changed
        if (isset($validated['name']) && $validated['name'] !== $fleet->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($fleet->thumbnail) {
                $this->fileUploadService->delete($fleet->thumbnail);
            }
            $validated['thumbnail'] = $this->uploadFile($request->file('thumbnail'), 'fleets');
        }

        // Handle multiple images upload
        if ($request->hasFile('images')) {
            // Delete old images
            if (is_array($fleet->images)) {
                foreach ($fleet->images as $oldImage) {
                    // $this->fileUploadService->delete($oldImage);
                }
            }

            $imagePaths = array_merge($fleet->images, []);
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $this->uploadFile($image, 'fleets');
            }
            $validated['images'] = $imagePaths;
        }

        $fleet->update($validated);

        return $this->dataResponse('Fleet updated successfully', FleetResource::make($fleet->fresh()));
    }

    /**
     * Remove the specified fleet
     */
    public function destroy(Fleet $fleet)
    {
        // Delete associated files
        if ($fleet->thumbnail) {
            $this->fileUploadService->delete($fleet->thumbnail);
        }

        if (is_array($fleet->images)) {
            foreach ($fleet->images as $image) {
                $this->fileUploadService->delete($image);
            }
        }

        $fleet->delete();

        return $this->successResponse('Fleet deleted successfully');
    }

    /**
     * Helper method to upload files
     */
    private function uploadFile($file, $directory = 'fleets')
    {
        $path = $this->fileUploadService->upload($file, $directory);

        return $path['file_path'];
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

        return $this->successResponse('Fleet order updated successfully');
    }

    /**
     * Toggle fleet active status
     */
    public function toggleStatus(Fleet $fleet)
    {
        $fleet->update(['is_active' => ! $fleet->is_active]);

        $status = $fleet->is_active ? 'activated' : 'deactivated';

        return $this->dataResponse("Fleet {$status} successfully", FleetResource::make($fleet));
    }
}
