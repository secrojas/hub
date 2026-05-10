<?php

namespace App\Models;

use App\Enums\TravelEstado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Travel extends Model
{
    protected $table = 'travels';

    protected $fillable = [
        'titulo',
        'destino',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'share_token',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'estado'       => TravelEstado::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Travel $travel) {
            $travel->share_token ??= (string) Str::uuid();
        });
    }

    public function segments(): HasMany
    {
        return $this->hasMany(TravelSegment::class)->orderBy('fecha_salida')->orderBy('hora_salida');
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(TravelAccommodation::class)->orderBy('fecha_checkin');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TravelActivity::class)->orderBy('fecha')->orderBy('hora');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TravelDocument::class)->orderByDesc('created_at');
    }

    public function getDuracionDiasAttribute(): int
    {
        return $this->fecha_inicio->diffInDays($this->fecha_fin) + 1;
    }
}
