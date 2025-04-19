<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\WhereDateStartEnd;

class Candidate extends Model
{
    use HasFactory, Filterable, AsSource;

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
        'id'         => Where::class,
        'first_name'=> Like::class,
        'last_name' => Like::class,
        'email'     => Like::class,
        'created_at'=> WhereDateStartEnd::class,
        'updated_at'=> WhereDateStartEnd::class,
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
        'created_at',
        'updated_at',
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