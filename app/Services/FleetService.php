<?php

namespace App\Services;

use App\Models\Fleet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FleetService
{
    public function __construct(private FileUploadService $fileUploadService) {}

    /**
     * Create a new fleet
     */
    public function create(array $validated, Request $request)
    {
        // Generate slug from name if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->fileUploadService->upload($request->file('thumbnail'), 'fleets')['file_path'];
        }

        // Handle multiple images upload
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $this->fileUploadService->upload($image, 'fleets')['file_path'];
            }
            $validated['images'] = $imagePaths;
        }

        // Set order if not provided (next highest order)
        if (empty($validated['order'])) {
            $validated['order'] = Fleet::max('order') + 1;
        }
        cache()->forget('all_active_fleets');

        return Fleet::create($validated);
    }

    /**
     * Update a fleet
     */
    public function update(Fleet $fleet, array $validated, Request $request): Fleet
    {
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
            $validated['thumbnail'] = $this->fileUploadService->upload($request->file('thumbnail'), 'fleets')['file_path'];
        }

        // Handle selective image deletion
        $existingImages = is_array($fleet->images) ? $fleet->images : [];
        $imagesToDelete = $request->input('images_deleted', []);

        if (! empty($imagesToDelete)) {
            foreach ($imagesToDelete as $imgPath) {
                if (in_array($imgPath, $existingImages)) {
                    $this->fileUploadService->delete($imgPath);
                    $existingImages = array_values(array_diff($existingImages, [$imgPath]));
                }
            }
        }

        // Handle new images upload (append to existing, not replace)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $existingImages[] = $this->fileUploadService->upload($image, 'fleets')['file_path'];
            }
        }

        $validated['images'] = $existingImages;

        $fleet->update($validated);

        cache()->forget('all_active_fleets');

        return $fleet->fresh();
    }

    /**
     * Delete a fleet
     */
    public function delete(Fleet $fleet): void
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

        cache()->forget('all_active_fleets');
    }
}
