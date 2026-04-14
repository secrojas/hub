<?php

namespace App\Enums;

enum KnowledgeSource: string
{
    case Chatgpt   = 'chatgpt';
    case Self      = 'self';
    case Docs      = 'docs';
    case Colleague = 'colleague';
}
