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
     * Handle attribute images upload and update validated data
     */
    private function handleAttributeImages($request, array $validated): array
    {
        $attributeImages = [
            'problem_solved' => 'problem-solved',
            'target_audience' => 'target-audience',
            'client_benefits' => 'client-benefits',
        ];

        foreach ($attributeImages as $attribute => $prefix) {
            $fileKey = "attributes.{$attribute}.image";

            if ($request->hasFile($fileKey)) {
                $filename = $this->handleImageUpload(
                    $request->file($fileKey),
                    $validated['slug'] . '-' . $prefix
                );

                // Set the filename in the nested array structure
                if (! isset($validated['attributes'])) {
                    $validated['attributes'] = [];
                }
                if (! isset($validated['attributes'][$attribute])) {
                    $validated['attributes'][$attribute] = [];
                }
                $validated['attributes'][$attribute]['image'] = $filename;
            }
        }

        return $validated;
    }

    /**
     * Handle attribute images update (delete old, upload new)
     */
    private function handleAttributeImagesUpdate($request, array $validated, $service): array
    {
        $attributeImages = [
            'problem_solved' => 'problem-solved',
            'target_audience' => 'target-audience',
            'client_benefits' => 'client-benefits',
        ];

        foreach ($attributeImages as $attribute => $prefix) {
            $fileKey = "attributes.{$attribute}.image";

            if ($request->hasFile($fileKey)) {
                // Delete old image if exists
                $oldImage = $service->attributes[$attribute]['image'] ?? null;
                if ($oldImage) {
                    Storage::disk('uploads')->delete('services/' . $oldImage);
                }

                // Upload new image
                $filename = $this->handleImageUpload(
                    $request->file($fileKey),
                    $validated['slug'] . '-' . $prefix
                );

                // Set the filename in the nested array structure
                if (! isset($validated['attributes'])) {
                    $validated['attributes'] = $service->attributes ?? [];
                }
                if (! isset($validated['attributes'][$attribute])) {
                    $validated['attributes'][$attribute] = $service->attributes[$attribute] ?? [];
                }
                $validated['attributes'][$attribute]['image'] = $filename;
            }
        }

        return $validated;
    }

    private function deleteServiceImages($service): void
    {
        // Delete banner image
        if ($service->banner_image) {
            Storage::disk('uploads')->delete('services/' . $service->banner_image);
        }

        // Delete attribute images
        if ($service->attributes) {
            $attributeTypes = ['problem_solved', 'target_audience', 'client_benefits'];

            foreach ($attributeTypes as $type) {
                $image = $service->attributes[$type]['image'] ?? null;
                if ($image) {
                    Storage::disk('uploads')->delete('services/' . $image);
                }
            }
        }

    }
}
