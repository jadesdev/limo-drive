<?php

namespace App\Actions;

use App\Models\Service;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceAction
{
    /**
     * Create a new service with the given data.
     */
    public function create(array $data): Service
    {
        return DB::transaction(function () use ($data) {
            // Handle file uploads
            $fileFields = [
                'banner_image',
                'problem_solved_image',
                'target_audience_image',
                'client_benefits_image',
            ];

            foreach ($fileFields as $field) {
                if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                    $data[$field] = $this->uploadFile($data[$field], 'services');
                }
            }

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Ensure arrays are properly encoded
            $data = $this->prepareArrayData($data);

            return Service::create($data);
        });
    }

    /**
     * Update an existing service with the given data.
     */
    public function update(Service $service, array $data): Service
    {
        return DB::transaction(function () use ($service, $data) {
            // Handle file uploads
            $fileFields = [
                'banner_image',
                'problem_solved_image',
                'target_audience_image',
                'client_benefits_image',
            ];

            foreach ($fileFields as $field) {
                if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                    // Delete old file if exists
                    if ($service->$field) {
                        Storage::disk('uploads')->delete($service->$field);
                    }
                    $data[$field] = $this->uploadFile($data[$field], 'services');
                } elseif (isset($data[$field]) && $data[$field] === null) {
                    // Handle file removal if field is explicitly set to null
                    if ($service->$field) {
                        Storage::disk('uploads')->delete($service->$field);
                        $data[$field] = null;
                    }
                }
            }

            // Ensure arrays are properly encoded
            $data = $this->prepareArrayData($data);

            $service->update($data);

            return $service->fresh();
        });
    }

    /**
     * Upload a file to the specified directory.
     */
    protected function uploadFile(UploadedFile $file, string $directory): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($directory, $filename, 'uploads');
    }

    /**
     * Prepare array data for storage.
     */
    protected function prepareArrayData(array $data): array
    {
        $arrayFields = ['features', 'technologies'];

        foreach ($arrayFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = array_values(array_filter($data[$field]));
            }
        }

        return $data;
    }
}
