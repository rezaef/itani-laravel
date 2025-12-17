<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CropPeriod extends Model
{
    protected $table = 'crop_periods';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class, 'periode_id');
    }
}
