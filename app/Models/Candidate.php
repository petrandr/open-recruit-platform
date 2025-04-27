<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Illuminate\Notifications\Notifiable;
use Orchid\Screen\AsSource;
use Orchid\Filters\Types\Where;
use App\Orchid\Filters\Types\InsensitiveLike;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\WhereDateStartEnd;

class Candidate extends Model
{
    use HasFactory, Filterable, AsSource, Notifiable;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile_number',
    ];

    /**
     * Attributes available for filtering in Orchid screens.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'          => Where::class,
        'first_name'  => InsensitiveLike::class,
        'last_name'   => InsensitiveLike::class,
        'email'       => InsensitiveLike::class,
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
        'first_name',
        'last_name',
        'email',
        'applications_count', // Allows sorting by number of applications
        'created_at',
        'updated_at',
        'full_name',        // Allows sorting by concatenated first and last name
    ];

    /**
     * Candidate has many job applications.
     */
    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'applicant_id');
    }
    /**
     * Get the candidate's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return sprintf("%s %s", $this->first_name, $this->last_name);
    }
}
