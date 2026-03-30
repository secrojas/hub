<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case Borrador  = 'borrador';
    case Enviado   = 'enviado';
    case Aceptado  = 'aceptado';
    case Rechazado = 'rechazado';
}
