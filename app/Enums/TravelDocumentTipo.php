<?php

namespace App\Enums;

enum TravelDocumentTipo: string
{
    case Pasaje  = 'pasaje';
    case Reserva = 'reserva';
    case Voucher = 'voucher';
    case Foto    = 'foto';
    case Otro    = 'otro';

    public function label(): string
    {
        return match($this) {
            self::Pasaje  => 'Pasaje',
            self::Reserva => 'Reserva',
            self::Voucher => 'Voucher',
            self::Foto    => 'Foto',
            self::Otro    => 'Otro',
        };
    }
}
