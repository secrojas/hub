<?php

namespace App\Models;

use App\Enums\TravelAccommodationTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelAccommodation extends Model
{
    protected $fillable = [
        'travel_id',
        'nombre',
        'tipo',
        'direccion',
        'telefono',
        'fecha_checkin',
        'hora_checkin',
        'fecha_checkout',
        'hora_checkout',
        'numero_reserva',
        'notas',
    ];

    protected $casts = [
        'fecha_checkin'  => 'date',
        'fecha_checkout' => 'date',
        'tipo'           => TravelAccommodationTipo::class,
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
