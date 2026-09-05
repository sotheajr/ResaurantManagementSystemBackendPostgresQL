<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    // Must match the exact database table name
    protected $table = 'payrolls';

    // Must match the primary key column in the database
    protected $primaryKey = 'payroll_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'month_year',
        'base_salary',
        'total_present_days',
        'total_absent_days',
        'total_ot_hours',
        'ot_amount',
        'bonus',
        'deductions',
        'net_salary',
        'payment_status',
        'payment_date',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'total_ot_hours' => 'decimal:2',
        'ot_amount' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date:Y-m-d',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The employee this payroll record belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}