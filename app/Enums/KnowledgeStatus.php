<?php

namespace App\Enums;

enum KnowledgeStatus: string
{
    case Draft    = 'draft';
    case Reviewed = 'reviewed';
    case Verified = 'verified';
    case Stale    = 'stale';
}
