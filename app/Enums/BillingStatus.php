<?php

namespace App\Enums;

enum BillingStatus: string
{
    case Pendiente = 'pendiente';
    case Pagado    = 'pagado';
    case Vencido   = 'vencido';
}
