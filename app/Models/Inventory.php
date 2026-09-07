<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    // Must match the exact database table name
    protected $table = 'inventory';

    // Must match the primary key column in the database
    protected $primaryKey = 'inventory_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'ingredient_name',
        'quantity',
        'unit',
        'minimum_stock',
        'image',
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Full public URL for the stored image.
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

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'inventory_id');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'minimum_stock');
    }
}
