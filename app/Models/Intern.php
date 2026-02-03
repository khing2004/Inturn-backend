<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intern extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'intern_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'admin_id',
        'university',
        'department',
        'supervisor',
        'start_date',
        'phone_number',
        'emergency_contact',
        'emergency_contact_name',
        'address',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Relationships
     */

    /**
     * Get the user that owns the intern.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the admin supervising this intern.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }

    /**
     * Get all submissions for this intern.
     */
    public function submissions()
    {
        return $this->hasMany(Submission::class, 'intern_id', 'intern_id');
    }

    /**
     * Get all attendance records for this intern.
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'intern_id', 'intern_id');
    }

    /**
     * Get all evaluations for this intern.
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'intern_id', 'intern_id');
    }

    /**
     * Scope a query to only include active interns.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope a query to only include pending interns.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope a query to only include inactive interns.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'Inactive');
    }
}