<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menu_items';
    protected $primaryKey = 'id';

    protected $fillable = [
        'category_id',
        'menu_name',
        'description',
        'price',
        'status',
        'discount_percent',
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

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
