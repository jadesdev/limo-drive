<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    /**
     * Upload an image
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
            'folder' => 'sometimes|string|max:255',
        ]);

        $file = $request->file('image');
        $folder = $request->input('folder', 'uploads');

        $result = $this->fileUploadService->upload($file, $folder);

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Delete an image
     */
    public function delete(string $fileId): JsonResponse
    {
        $success = $this->fileUploadService->delete($fileId);

        if (! $success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }
}
