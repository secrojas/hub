<?php

namespace App\Enums;

enum KnowledgeType: string
{
    case Concept  = 'concept';
    case Flow     = 'flow';
    case Bug      = 'bug';
    case Decision = 'decision';
    case Runbook  = 'runbook';
    case Glossary = 'glossary';
}
