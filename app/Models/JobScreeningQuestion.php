<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\WhereDateStartEnd;

class JobScreeningQuestion extends Model
{
    use HasFactory, Filterable, AsSource;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'job_id',
        'question_text',
        'question_type',
        'min_value',
    ];

    /**
     * Attributes available for filtering in Orchid screens.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'         => Where::class,
        'job_id'     => Where::class,
        'created_at' => WhereDateStartEnd::class,
        'updated_at' => WhereDateStartEnd::class,
    ];

    /**
     * Attributes available for sorting in Orchid screens.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'job_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Screening question belongs to a JobListing.
     */
    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'job_id');
    }

    /**
     * Screening question has many answers.
     */
    public function answers()
    {
        return $this->hasMany(JobApplicationAnswer::class, 'question_id');
    }
}