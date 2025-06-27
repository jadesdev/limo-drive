<?php

namespace App\Actions;

use App\Models\Service;
use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceAction
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    /**
     * Create a new service with the given data.
     */
    public function create(array $data): Service
    {
        return DB::transaction(function () use ($data) {
            $fileFields = [
                'banner_image',
                'problem_solved_image',
                'target_audience_image',
                'client_benefits_image',
            ];

            foreach ($fileFields as $field) {
                if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                    $data[$field] = $this->fileUploadService->upload($data[$field], 'services')['file_path'];
                }
            }

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }
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
            $fileFields = [
                'banner_image',
                'problem_solved_image',
                'target_audience_image',
                'client_benefits_image',
            ];

            foreach ($fileFields as $field) {
                if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                    if ($service->$field) {
                        $this->fileUploadService->delete($service->$field);
                    }
                    $data[$field] = $this->fileUploadService->upload($data[$field], 'services')['file_path'];
                }
            }

            $data = $this->prepareArrayData($data);

            $service->update($data);

            return $service->fresh();
        });
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
