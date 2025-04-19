<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\WhereDateStartEnd;

class ApplicationTracking extends Model
{
    use Filterable, AsSource;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'application_tracking';

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'application_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'utm_id',
        'utm_adgroup',
        'utm_placement',
        'utm_device',
        'utm_creative',
        'utm_referrer',
        'utm_other',
        'gclid',
        'fbclid',
    ];

    /**
     * Attributes available for filtering in Orchid screens.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'             => Where::class,
        'application_id' => Where::class,
        'utm_source'     => Like::class,
        'utm_campaign'   => Like::class,
        'created_at'     => WhereDateStartEnd::class,
    ];

    /**
     * Attributes available for sorting in Orchid screens.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'application_id',
        'utm_source',
        'utm_campaign',
        'created_at',
        'updated_at',
    ];

    /**
     * Tracking record belongs to a JobApplication.
     */
    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }
}