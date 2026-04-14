<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'horas',
        'source_url',
    ];

    protected function casts(): array
    {
        return [
            'estado'             => TaskStatus::class,
            'prioridad'          => TaskPriority::class,
            'fecha_limite'       => 'date',
            'fecha_finalizacion' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Task $task) {
            if ($task->isDirty('estado')) {
                if ($task->estado === TaskStatus::Finalizado) {
                    $task->fecha_finalizacion = now();
                } elseif ($task->estado !== TaskStatus::Archivado) {
                    $task->fecha_finalizacion = null;
                }
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }
}
