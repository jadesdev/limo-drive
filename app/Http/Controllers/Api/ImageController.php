<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImageKitService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    protected $imageKitService;

    public function __construct(ImageKitService $imageKitService)
    {
        $this->imageKitService = $imageKitService;
    }

    /**
     * Upload an image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB max
            'folder' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('image');
        $folder = $request->input('folder', 'uploads');
        
        $result = $this->imageKitService->upload($file, $folder);

        if (!$result) {
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
     *
     * @param string $fileId
     * @return JsonResponse
     */
    public function delete(string $fileId): JsonResponse
    {
        $success = $this->imageKitService->delete($fileId);

        if (!$success) {
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
