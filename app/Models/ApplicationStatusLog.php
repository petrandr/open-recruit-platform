<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use App\Models\JobApplication;
use App\Models\User;

class ApplicationStatusLog extends Model
{
    use AsSource, Filterable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'application_status_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'application_id',
        'from_status',
        'to_status',
        'changed_by',
    ];

    /**
     * Get the application that owns the status log.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    /**
     * Get the user who changed the status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}