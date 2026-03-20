<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'client_id',
        'descripcion',
        'prioridad',
        'estado',
        'fecha_limite',
    ];

    protected function casts(): array
    {
        return [
            'estado'       => TaskStatus::class,
            'prioridad'    => TaskPriority::class,
            'fecha_limite' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
