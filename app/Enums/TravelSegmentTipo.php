<?php

namespace App\Enums;

enum TravelSegmentTipo: string
{
    case Avion = 'avion';
    case Tren  = 'tren';
    case Micro = 'micro';
    case Barco = 'barco';
    case Auto  = 'auto';
    case Otro  = 'otro';

    public function label(): string
    {
        return match($this) {
            self::Avion => 'Avión',
            self::Tren  => 'Tren',
            self::Micro => 'Micro / Ómnibus',
            self::Barco => 'Barco / Ferry',
            self::Auto  => 'Auto',
            self::Otro  => 'Otro',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Avion => '✈️',
            self::Tren  => '🚆',
            self::Micro => '🚌',
            self::Barco => '⛴️',
            self::Auto  => '🚗',
            self::Otro  => '🚀',
        };
    }
}
