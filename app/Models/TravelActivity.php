<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelActivity extends Model
{
    protected $fillable = [
        'travel_id',
        'fecha',
        'hora',
        'titulo',
        'descripcion',
        'lugar',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }
}
