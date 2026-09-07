<?php

namespace App\Traits;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

trait UploadImageTrait
{
    protected $cloudinary;

    protected function getCloudinaryInstance(): Cloudinary
    {
        if (!$this->cloudinary) {
            $cloudUrl = config('cloudinary.cloud_url') ?: env('CLOUDINARY_URL');
            
            if ($cloudUrl) {
                Configuration::instance($cloudUrl);
                $this->cloudinary = new Cloudinary(Configuration::instance());
            } else {
                throw new \Exception('Cloudinary configuration not found. Please set CLOUDINARY_URL in .env file.');
            }
        }
        
        return $this->cloudinary;
    }

    /**
     * Upload an image to Cloudinary
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string The secure HTTPS URL of the uploaded image
     */
    public function uploadImage(UploadedFile $file, string $folder = 'uploads'): string
    {
        try {
            $cloudinary = $this->getCloudinaryInstance();
            $uploadApi = $cloudinary->uploadApi();

            $defaultFolder = config('cloudinary.default_folder', 'restaurant-management');
            $folderPath = trim($defaultFolder, '/') . '/' . trim($folder, '/');

            $result = $uploadApi->upload(
                $file->getRealPath(),
                [
                    'folder' => $folderPath,
                    'resource_type' => 'image',
                    'use_filename' => true,
                    'unique_filename' => false,
                ]
            );

            $secureUrl = $result['secure_url'];
            
            Log::info('Image uploaded to Cloudinary', [
                'folder' => $folderPath,
                'url' => $secureUrl,
            ]);

            return $secureUrl;
        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed: ' . $e->getMessage(), [
                'folder' => $folder,
                'file' => $file->getClientOriginalName(),
            ]);
            throw new \Exception('Failed to upload image: ' . $e->getMessage());
        }
    }

    /**
     * Delete an image from Cloudinary
     *
     * @param string $imageUrl The full Cloudinary URL of the image
     * @return bool
     */
    public function deleteImage(string $imageUrl): bool
    {
        try {
            // Extract public_id from the Cloudinary URL
            // URL format: https://res.cloudinary.com/cloud_name/image/upload/v1234567890/folder/public_id.ext
            $parsedUrl = parse_url($imageUrl);
            $path = $parsedUrl['path'] ?? '';
            
            // Remove the leading slash and extract the path after /image/upload/
            $pathParts = explode('/image/upload/', $path);
            if (count($pathParts) < 2) {
                return false;
            }
            
            $uploadPath = $pathParts[1];
            
            // Remove version number if present (v1234567890/)
            $uploadPath = preg_replace('/^v\d+\//', '', $uploadPath);
            
            // Get the folder structure and filename
            $defaultFolder = config('cloudinary.default_folder', 'restaurant-management');
            $publicId = $uploadPath;
            
            // Remove extension to get public_id
            $pathInfo = pathinfo($publicId);
            $publicId = $pathInfo['dirname'] . '/' . $pathInfo['filename'];
            $publicId = ltrim($publicId, '/');

            $cloudinary = $this->getCloudinaryInstance();
            $uploadApi = $cloudinary->uploadApi();

            $result = $uploadApi->destroy($publicId);
            
            Log::info('Image deleted from Cloudinary', [
                'public_id' => $publicId,
                'result' => $result,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Cloudinary delete failed: ' . $e->getMessage(), [
                'url' => $imageUrl,
            ]);
            return false;
        }
    }

    /**
     * Extract public_id from Cloudinary URL
     *
     * @param string $imageUrl
     * @return string|null
     */
    public function getPublicIdFromUrl(string $imageUrl): ?string
    {
        try {
            $parsedUrl = parse_url($imageUrl);
            $path = $parsedUrl['path'] ?? '';
            
            $pathParts = explode('/image/upload/', $path);
            if (count($pathParts) < 2) {
                return null;
            }
            
            $uploadPath = $pathParts[1];
            $uploadPath = preg_replace('/^v\d+\//', '', $uploadPath);
            
            $pathInfo = pathinfo($uploadPath);
            $publicId = $pathInfo['dirname'] . '/' . $pathInfo['filename'];
            
            return ltrim($publicId, '/');
        } catch (\Exception $e) {
            return null;
        }
    }
}
