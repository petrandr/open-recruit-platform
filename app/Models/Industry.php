<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use App\Models\JobListing;

class Industry extends Model
{
    use HasFactory, AsSource;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Industry has many JobListings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobListings()
    {
        return $this->hasMany(JobListing::class);
    }
}