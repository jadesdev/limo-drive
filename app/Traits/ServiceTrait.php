<?php

namespace App\Traits;

use Storage;

trait ServiceTrait
{
    /**
     * Handle individual image upload
     */
    private function handleImageUpload($file, $namePrefix): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = $namePrefix . '-' . time() . '.' . $extension;

        // Store in public disk under services folder
        $file->storeAs('services', $filename, 'uploads');

        return $filename;
    }

    /**
     * Handle banner image update or removal
     */
    private function handleBannerImageUpdate($request, array &$validated, $service): void
    {
        // Check if user wants to remove banner image
        if ($request->input('remove_banner_image', false)) {
            if ($service->banner_image) {
                Storage::disk('uploads')->delete('services/' . $service->banner_image);
            }
            $validated['banner_image'] = null;

            return;
        }

        // Handle new banner image upload
        if ($request->hasFile('banner_image')) {
            // Delete old banner image if exists
            if ($service->banner_image) {
                Storage::disk('uploads')->delete('services/' . $service->banner_image);
            }

            $validated['banner_image'] = $this->handleImageUpload(
                $request->file('banner_image'),
                'banner-' . $validated['slug']
            );
        }
    }

    /**
     * Delete service images
     */
    private function deleteServiceImages($service): void
    {
        // Delete banner image
        if ($service->banner_image) {
            Storage::disk('uploads')->delete('services/' . $service->banner_image);
        }
    }
}
