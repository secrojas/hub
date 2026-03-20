<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Task;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'email',
        'empresa',
        'telefono',
        'stack_tecnologico',
        'estado',
        'notas',
        'fecha_inicio',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
