<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\WhereDateStartEnd;

class JobApplicationAnswer extends Model
{
    use Filterable, AsSource;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'application_id',
        'question_id',
        'answer_text',
    ];

    /**
     * Attributes available for filtering in Orchid screens.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'             => Where::class,
        'application_id' => Where::class,
        'question_id'    => Where::class,
        'created_at'     => WhereDateStartEnd::class,
        'updated_at'     => WhereDateStartEnd::class,
    ];

    /**
     * Attributes available for sorting in Orchid screens.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'application_id',
        'question_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Answer belongs to a JobApplication.
     */
    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    /**
     * Answer belongs to a JobScreeningQuestion.
     */
    public function question()
    {
        return $this->belongsTo(JobScreeningQuestion::class, 'question_id');
    }
}