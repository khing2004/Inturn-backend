<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'admin_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * Relationships
     */

    /**
     * Get the user that owns the admin.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get all interns supervised by this admin.
     */
    public function interns()
    {
        return $this->hasMany(Intern::class, 'admin_id', 'admin_id');
    }

    /**
     * Get all evaluations created by this admin.
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'admin_id', 'admin_id');
    }
}
