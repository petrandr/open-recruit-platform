<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\WhereDateStartEnd;

class JobApplication extends Model
{
    use HasFactory, Filterable, AsSource;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'job_id',
        'applicant_id',
        'cv_path',
        'cv_disk',
        'location',
        'status',
        'rejection_sent',
        'notice_period',
        'desired_salary',
        'salary_currency',
        'linkedin_profile',
        'github_profile',
        'how_heard',
        'submitted_at',
    ];

    /**
     * Attribute casting.
     *
     * @var array
     */
    protected $casts = [
        'rejection_sent' => 'boolean',
        'desired_salary' => 'decimal:2',
        'submitted_at'   => 'datetime',
    ];

    /**
     * Attributes available for filtering in Orchid screens.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'           => Where::class,
        'status'       => Where::class,
        'submitted_at' => WhereDateStartEnd::class,
        'created_at'   => WhereDateStartEnd::class,
        'updated_at'   => WhereDateStartEnd::class,
    ];

    /**
     * Attributes available for sorting in Orchid screens.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'status',
        'submitted_at',
        'created_at',
        'updated_at',
        'desired_salary',
    ];

    /**
     * JobApplication belongs to a JobListing.
     */
    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'job_id');
    }

    /**
     * JobApplication belongs to a Candidate.
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'applicant_id');
    }

    /**
     * JobApplication has many answers.
     */
    public function answers()
    {
        return $this->hasMany(JobApplicationAnswer::class, 'application_id');
    }

    /**
     * JobApplication has one tracking record.
     */
    public function tracking()
    {
        return $this->hasOne(ApplicationTracking::class, 'application_id');
    }
}