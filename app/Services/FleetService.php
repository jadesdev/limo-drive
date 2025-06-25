<?php

namespace App\Services;

use App\Models\Fleet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FleetService
{
    /**
     * Create a new fleet
     */
    public function create(array $validated, Request $request): Fleet
    {
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
                Storage::disk('uploads')->delete($fleet->thumbnail);
            }
            $validated['thumbnail'] = $this->uploadFile($request->file('thumbnail'), 'fleets');
        }

        // Handle selective image deletion
        $existingImages = is_array($fleet->images) ? $fleet->images : [];
        $imagesToDelete = $request->input('images_deleted', []);

        if (! empty($imagesToDelete)) {
            foreach ($imagesToDelete as $imgPath) {
                if (in_array($imgPath, $existingImages)) {
                    Storage::disk('uploads')->delete($imgPath);
                    $existingImages = array_values(array_diff($existingImages, [$imgPath]));
                }
            }
        }

        // Handle new images upload (append to existing, not replace)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $existingImages[] = $this->uploadFile($image, 'fleets');
            }
        }

        $validated['images'] = $existingImages;

        $fleet->update($validated);

        return $fleet->fresh();
    }

    /**
     * Delete a fleet
     */
    public function delete(Fleet $fleet): void
    {
        // Delete associated files
        if ($fleet->thumbnail) {
            Storage::disk('uploads')->delete($fleet->thumbnail);
        }

        if (is_array($fleet->images)) {
            foreach ($fleet->images as $image) {
                Storage::disk('uploads')->delete($image);
            }
        }

        $fleet->delete();
    }

    /**
     * Helper method to upload files
     */
    private function uploadFile($file, $directory = 'fleets')
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'uploads');

        return $path;
    }
}
