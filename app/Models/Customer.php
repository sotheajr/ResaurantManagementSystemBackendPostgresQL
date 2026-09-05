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
        return $this->profile_image ? asset('storage/' . $this->profile_image) : null;
    }
}
