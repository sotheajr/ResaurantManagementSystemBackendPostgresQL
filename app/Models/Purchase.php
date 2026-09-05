<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';
    protected $primaryKey = 'purchase_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'supplier_id',
        'purchase_date',
        'total',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }
}