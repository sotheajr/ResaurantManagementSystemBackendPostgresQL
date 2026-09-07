<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Cloudinary integration.
    | The CLOUDINARY_URL environment variable is used for authentication.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cloudinary URL
    |--------------------------------------------------------------------------
    |
    | The Cloudinary URL contains all the credentials needed to connect
    | to your Cloudinary account. It should be in the format:
    | cloudinary://api_key:api_secret@cloud_name
    |
    */

    'cloud_url' => env('CLOUDINARY_URL'),

    /*
    |--------------------------------------------------------------------------
    | Cloud Name
    |--------------------------------------------------------------------------
    |
    | Your Cloudinary cloud name. This is extracted from the CLOUDINARY_URL
    | but can be overridden here if needed.
    |
    */

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Cloudinary API key. This is extracted from the CLOUDINARY_URL
    | but can be overridden here if needed.
    |
    */

    'api_key' => env('CLOUDINARY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | API Secret
    |--------------------------------------------------------------------------
    |
    | Your Cloudinary API secret. This is extracted from the CLOUDINARY_URL
    | but can be overridden here if needed.
    |
    */

    'api_secret' => env('CLOUDINARY_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Secure Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, all URLs returned will use HTTPS.
    |
    */

    'secure' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Upload Folder
    |--------------------------------------------------------------------------
    |
    | The default folder in Cloudinary where images will be uploaded.
    |
    */

    'default_folder' => env('CLOUDINARY_DEFAULT_FOLDER', 'restaurant-management'),
];
