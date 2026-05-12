<?php

namespace App\Enums\Finance;

enum ExpenseCategory: string
{
    case Alquiler      = 'alquiler';
    case Cochera       = 'cochera';
    case Servicios     = 'servicios';
    case Tarjetas      = 'tarjetas';
    case Credito       = 'credito';
    case Suscripciones = 'suscripciones';
    case Alimentacion  = 'alimentacion';
    case Transporte    = 'transporte';
    case Salud         = 'salud';
    case Entretenimiento = 'entretenimiento';
    case Otros         = 'otros';

    public function label(): string
    {
        return match($this) {
            self::Alquiler       => 'Alquiler',
            self::Cochera        => 'Cochera',
            self::Servicios      => 'Servicios',
            self::Tarjetas       => 'Tarjetas',
            self::Credito        => 'Crédito',
            self::Suscripciones  => 'Suscripciones',
            self::Alimentacion   => 'Alimentación',
            self::Transporte     => 'Transporte',
            self::Salud          => 'Salud',
            self::Entretenimiento => 'Entretenimiento',
            self::Otros          => 'Otros',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Alquiler       => 'violet',
            self::Cochera        => 'slate',
            self::Servicios      => 'blue',
            self::Tarjetas       => 'orange',
            self::Credito        => 'red',
            self::Suscripciones  => 'emerald',
            self::Alimentacion   => 'yellow',
            self::Transporte     => 'cyan',
            self::Salud          => 'pink',
            self::Entretenimiento => 'purple',
            self::Otros          => 'slate',
        };
    }
}
