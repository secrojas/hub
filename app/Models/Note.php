<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'titulo',
        'contenido',
        'extracto',
        'esta_fijada',
        'en_dashboard',
    ];

    protected function casts(): array
    {
        return [
            'esta_fijada'  => 'boolean',
            'en_dashboard' => 'boolean',
        ];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(NoteFolder::class, 'folder_id');
    }
}
