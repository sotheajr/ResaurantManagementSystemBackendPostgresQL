<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';
    protected $primaryKey = 'reservation_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'customer_id',
        'table_id',
        'reservation_date',
        'guest_number',
        'status',
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
        'guest_number' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }
}