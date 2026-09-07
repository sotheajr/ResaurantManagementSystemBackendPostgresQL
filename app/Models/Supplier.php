<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';
    protected $primaryKey = 'supplier_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'supplier_name',
        'phone',
        'address',
        'logo',
    ];

    protected $appends = ['logo_url'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }

    /**
     * Full public URL for the stored logo image.
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null;
        }

        // If the logo is already an absolute URL (e.g., Cloudinary), return it directly
        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }

        // Otherwise, it's a local storage path
        return asset('storage/' . $this->logo);
    }
}
