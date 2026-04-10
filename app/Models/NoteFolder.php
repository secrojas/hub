<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'parent_id',
        'color',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NoteFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NoteFolder::class, 'parent_id')->orderBy('nombre');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'folder_id');
    }
}
