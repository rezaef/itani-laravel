<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorNotification extends Model
{
    protected $table = 'sensor_notifications';

    protected $fillable = [
        'user_id',
        'dedupe_key',
        'level',
        'title',
        'message',
        'state',
        'occurrences',
        'last_seen_at',
        'last_triggered_at',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'last_seen_at' => 'datetime',
        'last_triggered_at' => 'datetime',
    ];
}