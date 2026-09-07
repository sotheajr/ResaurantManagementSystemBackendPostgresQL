<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Table extends Model
{
    use HasFactory;

    protected $table = 'tables';
    protected $primaryKey = 'id';

    protected $fillable = [
        'table_number',
        'capacity',
        'location',
        'status',
        'image',
    ];

    protected $appends = ['image_url'];

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

    /**
     * Scope: filter only available tables
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available');
    }

    /**
     * Scope: order by table_number ascending
     */
    public function scopeOrderByTableNumber($query)
    {
        return $query->orderBy('table_number', 'asc');
    }
}
