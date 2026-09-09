<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $primaryKey = 'id';

    protected $fillable = [
        'table_id',
        'customer_id',
        'user_id',
        'waiter_id',
        'total_amount',
        'status',
        'payment_status',
        'checkout_by',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The table associated with this order.
     */
    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    /**
     * The customer who placed this order.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * The user who created / keyed in this order.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * The assigned waiter for this order.
     */
    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id', 'user_id');
    }

    /**
     * Backward-compatible alias for creator.
     */
    public function user()
    {
        return $this->creator();
    }

    /**
     * The cashier who completed the checkout for this order.
     */
    public function checkoutUser()
    {
        return $this->belongsTo(User::class, 'checkout_by', 'user_id');
    }

    /**
     * The items in this order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    /**
     * The payments recorded for this order.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id', 'id');
    }

    /**
     * The most recent payment for this order.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id', 'id')->latestOfMany();
    }

    /**
     * Scope: Active orders (pending, preparing, ready)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'preparing', 'ready']);
    }

    /**
     * Scope: Completed orders
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
