<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationComment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'application_id',
        'comment_text',
        'source',
        'user_id',
    ];

    /**
     * Comment belongs to an application.
     */
    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }
    /**
     * Comment added by a user.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}