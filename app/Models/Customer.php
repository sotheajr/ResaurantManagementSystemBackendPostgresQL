<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'customer_name',
        'phone',
        'email',
        'address',
        'profile_image',
    ];

    protected $appends = ['profile_image_url'];

    public function getProfileImageUrlAttribute()
    {
        if (!$this->profile_image) {
            return null;
        }

        // If the image is already an absolute URL (e.g., Cloudinary), return it directly
        if (str_starts_with($this->profile_image, 'http://') || str_starts_with($this->profile_image, 'https://')) {
            return $this->profile_image;
        }

        // Otherwise, it's a local storage path
        return asset('storage/' . $this->profile_image);
    }
}
