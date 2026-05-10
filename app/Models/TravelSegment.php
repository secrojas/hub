<?php

namespace App\Models;

use App\Enums\TravelSegmentTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelSegment extends Model
{
    protected $fillable = [
        'travel_id',
        'orden',
        'tipo',
        'origen',
        'destino',
        'fecha_salida',
        'hora_salida',
        'fecha_llegada',
        'hora_llegada',
        'empresa',
        'numero_servicio',
        'numero_asiento',
        'localizador',
        'numero_anden',
        'notas',
    ];

    protected $casts = [
        'fecha_salida'  => 'date',
        'fecha_llegada' => 'date',
        'tipo'          => TravelSegmentTipo::class,
    ];

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TravelDocument::class);
    }
}
