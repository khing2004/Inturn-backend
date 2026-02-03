<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daily_Report extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daily_reports';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'report_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'report_title',
        'accomplishments',
        'tasks_completed',
        'challenges',
    ];

    /**
     * Relationships
     */

    /**
     * Get the submission that owns the daily report.
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'document_id', 'document_id');
    }

    /**
     * Get the intern through the submission.
     */
    public function intern()
    {
        return $this->hasOneThrough(
            Intern::class,
            Submission::class,
            'document_id', // Foreign key on submissions table
            'intern_id',   // Foreign key on interns table
            'document_id', // Local key on daily_reports table
            'intern_id'    // Local key on submissions table
        );
    }
}