<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileUploadService
{
    protected $provider;

    public function __construct()
    {
        $this->provider = config('filesystems.provider', 'local');
    }

    public function upload($file, $folder = 'uploads')
    {
        if ($this->provider === 'imagekit') {
            return app(ImageKitService::class)->upload($file, $folder);
        }
        if ($this->provider === 'local') {
            return $this->localUpload($file, $folder);
        }
    }

    public function delete($fileId)
    {
        if ($this->provider === 'imagekit') {
            return app(ImageKitService::class)->delete($fileId);
        }
        if ($this->provider === 'local') {
            return $this->localDelete($fileId);
        }
    }

    public function localUpload(UploadedFile $file, $folder = 'uploads')
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '-' . time() . '.' . $extension;

        // Store in public disk under services folder
        $uploadedFile = $file->storeAs($folder, $filename, 'uploads');

        return [
            'url' => $this->localAssetUrl($uploadedFile),
            'file_path' => $uploadedFile,
            'file_name' => $filename,
            'file_type' => $file->getMimeType(),
            'extension' => $extension,
            'size' => $file->getSize(),
        ];
    }

    public function localDelete($fileId)
    {
        $path = public_path('uploads/' . $fileId);
        if (file_exists($path)) {
            unlink($path);
        }

        return $path;
    }

    public function localAssetUrl(string $path, $secure = null)
    {
        if (PHP_SAPI == 'cli-server') {
            return app('url')->asset('uploads/' . $path, $secure);
        }

        return app('url')->asset('public/uploads/' . $path, $secure);
    }
}
