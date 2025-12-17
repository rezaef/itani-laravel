<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harvest extends Model
{
    protected $table = 'harvests';

    protected $fillable = [
        'periode_id',
        'tanggal_panen',
        'jenis_tanaman',
        'jumlah_panen',
        'catatan',
    ];

    protected $casts = [
        'tanggal_panen' => 'date',
        'jumlah_panen'  => 'float',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(CropPeriod::class, 'periode_id');
    }
}
