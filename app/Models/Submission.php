<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'submissions';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'document_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'intern_id',
        'type',
        'file_name',
        'file_path',
        'date_submitted',
        'status',
        'admin_remarks',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_submitted' => 'datetime',
        'type' => 'string',
        'status' => 'string',
    ];

    /**
     * Relationships
     */

    /**
     * Get the intern that owns the submission.
     */
    public function intern()
    {
        return $this->belongsTo(Intern::class, 'intern_id', 'intern_id');
    }

    /**
     * Get the daily report associated with this submission.
     */
    public function dailyReport()
    {
        return $this->hasOne(Daily_Report::class, 'document_id', 'document_id');
    }

    /**
     * Scope a query to only include pending submissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope a query to only include verified submissions.
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'Verified');
    }

    /**
     * Scope a query to only include rejected submissions.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }

    /**
     * Scope a query to only include daily reports.
     */
    public function scopeDailyReports($query)
    {
        return $query->where('type', 'Daily Report');
    }

    /**
     * Scope a query to only include documents.
     */
    public function scopeDocuments($query)
    {
        return $query->where('type', 'Document');
    }
}