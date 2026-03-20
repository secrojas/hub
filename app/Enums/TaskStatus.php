<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Backlog     = 'backlog';
    case EnProgreso  = 'en_progreso';
    case EnRevision  = 'en_revision';
    case Finalizado  = 'finalizado';
}
