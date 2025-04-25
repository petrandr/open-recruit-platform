<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;

class MailLog extends Model
{
    use HasFactory, AsSource;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'class',
        'channel',
        'subject',
        'recipients',
        'cc',
        'bcc',
        'body',
        'notifiable_type',
        'notifiable_id',
        'data',
        'sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'recipients'      => 'array',
        'cc'              => 'array',
        'bcc'             => 'array',
        'data'            => 'array',
        'sent_at'         => 'datetime',
    ];
}