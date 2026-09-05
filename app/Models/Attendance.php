<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Standard work hours per day; anything beyond this is overtime.
     */
    const STANDARD_HOURS = 8;

    // Must match the exact database table name
    protected $table = 'attendances';

    // Must match the primary key column in the database
    protected $primaryKey = 'attendance_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_hours',
        'status',
        'notes',
    ];

    /**
     * Appended attributes included in the JSON response.
     */
    protected $appends = ['work_hours', 'ot_hours'];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'total_hours' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The employee this attendance record belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Total worked hours (mirrors total_hours for the API contract).
     */
    public function getWorkHoursAttribute()
    {
        return (float) $this->total_hours;
    }

    /**
     * Overtime hours beyond the standard work day.
     */
    public function getOtHoursAttribute()
    {
        return max(0, round((float) $this->total_hours - self::STANDARD_HOURS, 2));
    }
}