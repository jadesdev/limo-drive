<?php

namespace App\Services;

use ImageKit\ImageKit as ImageKitSDK;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageKitService
{
    protected $imageKit;

    public function __construct()
    {
        $this->imageKit = new ImageKitSDK(
            config('imagekit.public_key'),
            config('imagekit.private_key'),
            config('imagekit.url_endpoint')
        );
    }

    /**
     * Upload an image to ImageKit
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string $fileName
     * @return array|null
     */
    public function upload(UploadedFile $file, string $folder = 'uploads', ?string $fileName = null)
    {
        try {
            if (!$fileName) {
                $fileName = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
            }

            $fileContent = file_get_contents($file->getRealPath());
            
            $response = $this->imageKit->uploadFile([
                'file' => $fileContent,
                'fileName' => $fileName,
                'folder' => $folder,
                'useUniqueFileName' => true,
                'overwriteFile' => false,
            ]);

            return [
                'url' => $response->result->url,
                'file_id' => $response->result->fileId,
                'file_path' => $response->result->filePath,
                'file_name' => $response->result->name,
                'file_type' => $response->result->fileType,
                'width' => $response->result->width ?? null,
                'height' => $response->result->height ?? null,
                'size' => $response->result->size,
            ];
        } catch (\Exception $e) {
            Log::error('ImageKit upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a file from ImageKit
     *
     * @param string $fileId
     * @return bool
     */
    public function delete(string $fileId): bool
    {
        try {
            $response = $this->imageKit->deleteFile($fileId);
            return $response->result === 'file deleted successfully';
        } catch (\Exception $e) {
            Log::error('ImageKit delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a URL for an image with optional transformations
     *
     * @param string $filePath
     * @param array $transformations
     * @return string
     */
    public function getUrl(string $filePath, array $transformations = []): string
    {
        try {
            return $this->imageKit->url([
                'path' => $filePath,
                'transformation' => $transformations,
            ]);
        } catch (\Exception $e) {
            Log::error('ImageKit URL generation failed: ' . $e->getMessage());
            return '';
        }
    }
}
