<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'attendance_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'intern_id',
        'work_date',
        'time_in',
        'time_out',
        'status',
        'total_hours',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'work_date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'status' => 'string',
    ];

    /**
     * Relationships
     */

    /**
     * Get the intern that owns the attendance record.
     */
    public function intern()
    {
        return $this->belongsTo(Intern::class, 'intern_id', 'intern_id');
    }

    /**
     * Scope a query to only include present attendance.
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'Present');
    }

    /**
     * Scope a query to only include late attendance.
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'Late');
    }

    /**
     * Scope a query to only include absent records.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'Absent');
    }

    /**
     * Scope a query to only include undertime records.
     */
    public function scopeUndertime($query)
    {
        return $query->where('status', 'Undertime');
    }

    /**
     * Scope a query for a specific date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }
}