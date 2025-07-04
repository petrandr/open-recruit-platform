<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;
use App\Models\AppointmentCalendar;

class User extends Authenticatable
{
//    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'array',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id' => Where::class,
        'name' => Like::class,
        'email' => Like::class,
        'updated_at' => WhereDateStartEnd::class,
        'created_at' => WhereDateStartEnd::class,
        'last_login_at' => WhereDateStartEnd::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'updated_at',
        'created_at',
        'last_login_at',
    ];

    /**
     * Data to index for Scout.
     */
    public function toSearchableArray(): array
    {
        return $this->only(['id', 'name', 'email']);
    }

    /**
     * Return the Orchid presenter instance.
     */
    public function presenter()
    {
        return new \App\Orchid\Presenters\UserPresenter($this);
    }

    /**
     * User has many appointment calendars.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointmentCalendars()
    {
        return $this->hasMany(AppointmentCalendar::class);
    }
    /**
     * Applications that have been shared with this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sharedApplications()
    {
        return $this->belongsToMany(
            \App\Models\JobApplication::class,
            'job_application_user',
            'user_id',
            'job_application_id'
        )->withTimestamps();
    }
}
