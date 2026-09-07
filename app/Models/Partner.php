<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    // Must match the exact database table name
    protected $table = 'partners';

    // Must match the primary key column in the database
    protected $primaryKey = 'partner_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'partner_name',
        'contact_name',
        'phone',
        'email',
        'address',
        'partnership_type',
        'status',
        'image',
    ];

    /**
     * Appended attributes included in the JSON response.
     */
    protected $appends = ['image_url'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Full public URL for the stored logo image.
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        // If the image is already an absolute URL (e.g., Cloudinary), return it directly
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        // Otherwise, it's a local storage path
        return asset('storage/' . $this->image);
    }
}