<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Ilike;
use Orchid\Screen\AsSource;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\WhereDateStartEnd;
use Illuminate\Support\Facades\Auth;
use App\Models\ApplicationStatusLog;

class JobApplication extends Model
{
    use HasFactory, Filterable, AsSource;

    /**
     * Boot the model and listen for status changes to log them.
     */
    protected static function booted(): void
    {
        static::updated(function (JobApplication $application) {
            if ($application->wasChanged('status')) {
                ApplicationStatusLog::create([
                    'application_id' => $application->id,
                    'from_status' => $application->getOriginal('status'),
                    'to_status' => $application->status,
                    'changed_by' => Auth::id(),
                ]);
            }
        });
    }

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
        'city',
        'country',
        'status',
        'rejection_sent',
        'notice_period',
        'desired_salary',
        'salary_currency',
        'linkedin_profile',
        'github_profile',
        'how_heard',
        'submitted_at',
        // Precomputed fit ratio (0 to 1)
        'fit_ratio',
    ];

    /**
     * Attribute casting.
     *
     * @var array
     */
    protected $casts = [
        'rejection_sent' => 'boolean',
        'desired_salary' => 'decimal:2',
        'submitted_at' => 'datetime',
        'fit_ratio' => 'float',
    ];

    /**
     * Attributes available for filtering in Orchid screens.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id' => Where::class,
        'status' => Where::class,
        'city' => Ilike::class,
        'country' => Ilike::class,
        'submitted_at' => WhereDateStartEnd::class,
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
        'status',
        'submitted_at',
        'created_at',
        'updated_at',
        'desired_salary',
        'city',
        'country',
        // Precomputed fit ratio
        'fit_ratio',
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
     * JobApplication has many comments.
     */
    public function comments()
    {
        return $this->hasMany(ApplicationComment::class, 'application_id');
    }

    /**
     * JobApplication has one tracking record.
     */
    public function tracking()
    {
        return $this->hasOne(ApplicationTracking::class, 'application_id');
    }
    
    /**
     * Get all status logs for this application.
     */
    public function statusLogs()
    {
        return $this->hasMany(ApplicationStatusLog::class, 'application_id')->orderBy('created_at');
    }

    /**
     * Calculate fit based on screening question answers.
     *
     * @return array{fit: string, fitClass: string, ratio: float, total: int, meets: int}
     */
    public function calculateFit(): array
    {
        $total = $this->answers->count();
        $meets = 0;
        foreach ($this->answers as $answer) {
            $question = $answer->question;
            if (!$question) {
                continue;
            }
            if ($question->question_type === 'yes/no') {
                if (strcasecmp($answer->answer_text, $question->min_value) === 0) {
                    $meets++;
                }
            } elseif ($question->question_type === 'number') {
                if ((float) $answer->answer_text >= (float) $question->min_value) {
                    $meets++;
                }
            }
        }
        return [
            'ratio' => $total > 0 ? $meets / $total : 0.0
        ];
    }

    /**
     * Accessor for fit label based on stored fit_ratio.
     *
     * @return string
     */
    public function getFitAttribute(): string
    {
        $ratio = $this->fit_ratio ?? 0;
        if ($ratio >= 0.8) {
            return 'Good fit';
        } elseif ($ratio >= 0.5) {
            return 'Maybe';
        }
        return 'Not a fit';
    }

    /**
     * Accessor for CSS class of fit label based on stored fit_ratio.
     *
     * @return string
     */
    public function getFitClassAttribute(): string
    {
        $ratio = $this->fit_ratio ?? 0;
        if ($ratio >= 0.8) {
            return 'success';
        } elseif ($ratio >= 0.5) {
            return 'warning';
        }
        return 'danger';
    }
}
