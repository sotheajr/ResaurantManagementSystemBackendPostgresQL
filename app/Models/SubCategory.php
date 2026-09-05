<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubCategory extends Model
{
    use HasFactory;

    protected $table = 'sub_categories';
    protected $primaryKey = 'sub_category_id';

    protected $fillable = [
        'category_id',
        'sub_category_name',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
