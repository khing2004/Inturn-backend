<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'notification_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'reference_type',
        'reference_id',
        'is_read',
        'type',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'is_read' => 'boolean',
        'reference_type' => 'string',
        'type' => 'string',
    ];

    /**
     * Relationships
     */

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the related submission (polymorphic-like behavior).
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'reference_id', 'document_id')
            ->where('reference_type', 'submission');
    }

    /**
     * Get the related attendance (polymorphic-like behavior).
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'reference_id', 'attendance_id')
            ->where('reference_type', 'attendance');
    }

    /**
     * Get the related evaluation (polymorphic-like behavior).
     */
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'reference_id', 'evaluation_id')
            ->where('reference_type', 'evaluation');
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to only include urgent notifications.
     */
    public function scopeUrgent($query)
    {
        return $query->where('Type', 'Urgent');
    }

    /**
     * Scope a query to only include warning notifications.
     */
    public function scopeWarning($query)
    {
        return $query->where('type', 'Warning');
    }

    /**
     * Scope a query to only include success notifications.
     */
    public function scopeSuccess($query)
    {
        return $query->where('type', 'Success');
    }

    /**
     * Scope a query to only include info notifications.
     */
    public function scopeInfo($query)
    {
        return $query->where('type', 'Info');
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread()
    {
        $this->is_read = false;
        $this->save();
    }
}