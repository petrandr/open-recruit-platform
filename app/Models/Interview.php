<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Interview extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'application_id',
        'interviewer_id',
        'scheduled_at',
        'status',
        'round',
        'mode',
        'location',
        'duration_minutes',
        'comments',
    ];

    /**
     * Attribute casting.
     *
     * @var array
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * Interview belongs to a JobApplication.
     */
    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    /**
     * Interview belongs to an interviewer (User).
     */
    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}