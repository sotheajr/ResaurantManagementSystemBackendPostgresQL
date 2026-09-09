<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_id',
        'cashier_id',
        'checkout_by',
        'payment_method',
        'transaction_id',
        'external_payment_id',
        'amount',
        'currency',
        'payment_status',
        'receipt_no',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Compatibility accessors: the frontend expects `payment_id` and
     * `payment_date` on payment records.
     */
    protected $appends = ['payment_id', 'payment_date'];

    public function getPaymentIdAttribute()
    {
        return $this->attributes['id'] ?? null;
    }

    public function getPaymentDateAttribute()
    {
        return $this->attributes['paid_at'] ?? $this->attributes['created_at'] ?? null;
    }

    /**
     * The order this payment settles.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * The cashier who processed this payment.
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id', 'user_id');
    }

    /**
     * Scope: paid payments only.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
}