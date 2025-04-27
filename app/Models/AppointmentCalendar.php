<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentCalendar extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'url',
    ];

    /**
     * Get the owning user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}