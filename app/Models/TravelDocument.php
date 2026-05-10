<?php

namespace App\Models;

use App\Enums\TravelDocumentTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelDocument extends Model
{
    protected $fillable = [
        'travel_id',
        'travel_segment_id',
        'travel_accommodation_id',
        'nombre',
        'tipo',
        'archivo_path',
        'mime_type',
        'tamanio',
        'notas',
    ];

    protected $casts = [
        'tipo' => TravelDocumentTipo::class,
    ];

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(TravelSegment::class, 'travel_segment_id');
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(TravelAccommodation::class, 'travel_accommodation_id');
    }

    public function getTamanioFormateadoAttribute(): string
    {
        $kb = $this->tamanio / 1024;
        if ($kb < 1024) {
            return round($kb, 1) . ' KB';
        }

        return round($kb / 1024, 2) . ' MB';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
