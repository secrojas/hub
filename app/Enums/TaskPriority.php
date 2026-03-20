<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Baja  = 'baja';
    case Media = 'media';
    case Alta  = 'alta';
}
