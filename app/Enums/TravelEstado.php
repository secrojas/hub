<?php

namespace App\Enums;

enum TravelEstado: string
{
    case Planificado = 'planificado';
    case EnCurso = 'en_curso';
    case Completado = 'completado';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match($this) {
            self::Planificado => 'Planificado',
            self::EnCurso     => 'En curso',
            self::Completado  => 'Completado',
            self::Cancelado   => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Planificado => 'blue',
            self::EnCurso     => 'violet',
            self::Completado  => 'green',
            self::Cancelado   => 'slate',
        };
    }
}
