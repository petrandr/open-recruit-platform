<?php

namespace App\Models;

use App\Orchid\Filters\Types\InsensitiveLike;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Industry;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Orchid\Platform\Models\Role;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\WhereDateStartEnd;

class JobListing extends Model
{
    use HasFactory, Filterable, AsSource;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'headline',
        'responsibilities',
        'requirements',
        'bonus',
        'benefits',
        'date_opened',
        'job_type',
        'workplace',
        'status',
        'location',
        'who_to_notify',
        'industry_id',
    ];

    /**
     * Attribute casting.
     *
     * @var array
     */
    protected $casts = [
        'workplace'     => 'array',
        'who_to_notify' => 'array',
        'date_opened'   => 'date',
    ];

    /**
     * Attributes available for filtering in Orchid screens.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'          => Where::class,
        'title'       => InsensitiveLike::class,
        'status'      => Where::class,
        'date_opened' => WhereDateStartEnd::class,
        'created_at'  => WhereDateStartEnd::class,
        'updated_at'  => WhereDateStartEnd::class,
    ];

    /**
     * Attributes available for sorting in Orchid screens.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'title',
        'status',
        'date_opened',
        'created_at',
        'updated_at',
    ];

    /**
     * JobListing has many JobApplication.
     */
    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    /**
     * JobListing has many screening questions.
     */
    public function screeningQuestions()
    {
        return $this->hasMany(JobScreeningQuestion::class, 'job_id');
    }
    /**
     * JobListing has many Roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'job_listing_role', 'job_listing_id', 'role_id');
    }
    /**
     * JobListing belongs to an Industry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }
    /**
     * Get the title with status in parentheses.
     *
     * @return string
     */
    public function getTitleWithStatusAttribute(): string
    {
        $status = $this->status;
        if ($status) {
            $status = ucfirst($status);
            return sprintf("%s (%s)", $this->title, $status);
        }
        return $this->title;
    }
}
