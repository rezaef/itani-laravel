<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorNotification extends Model
{
    protected $table = 'sensor_notifications';

    protected $fillable = [
        'user_id',
        'level',
        'title',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}