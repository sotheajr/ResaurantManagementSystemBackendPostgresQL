<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait UploadImageTrait
{
    public function uploadImage(UploadedFile $file, $folder = 'uploads')
    {
        return $file->store($folder, 'public');
    }

    public function deleteImage($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
