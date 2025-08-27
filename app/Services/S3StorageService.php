<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class S3StorageService
{
    public function storeFile($file, $directory, $filename = null)
    {
        $filename = $filename ?: $file->getClientOriginalName();
        $path = Storage::disk('s3')->putFileAs(
            $directory,
            $file,
            $filename
        );

        return [
            'path' => $path,
            'url' => Storage::disk('s3')->url($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize()
        ];
    }

    public function deleteFile($path)
    {
        return Storage::disk('s3')->delete($path);
    }

    public function getFile($path)
    {
        return Storage::disk('s3')->get($path);
    }

    public function getFileUrl($path)
    {
        return Storage::disk('s3')->url($path);
    }
}
