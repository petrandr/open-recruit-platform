<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
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
        'title'       => Like::class,
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