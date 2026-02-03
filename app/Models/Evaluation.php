<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'evaluations';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'EvaluationID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'intern_id',
        'admin_id',
        'technical_skills_rating',
        'communication_rating',
        'admin_comments',
        'evaluation_date',
        'period',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'evaluation_date' => 'date',
        'technical_skills_rating' => 'integer',
        'communication_rating' => 'integer',
        'period' => 'string',
    ];

    /**
     * Relationships
     */

    /**
     * Get the intern that owns the evaluation.
     */
    public function intern()
    {
        return $this->belongsTo(Intern::class, 'intern_id', 'intern_id');
    }

    /**
     * Get the admin that created the evaluation.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }

    /**
     * Scope a query to only include weekly evaluations.
     */
    public function scopeWeekly($query)
    {
        return $query->where('period', 'Weekly');
    }

    /**
     * Scope a query to only include monthly evaluations.
     */
    public function scopeMonthly($query)
    {
        return $query->where('period', 'Monthly');
    }

    /**
     * Get the average rating for this evaluation.
     */
    public function getAverageRatingAttribute()
    {
        return ($this->technical_skills_rating + $this->communication_rating) / 2;
    }
}