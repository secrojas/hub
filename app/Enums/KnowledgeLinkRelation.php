<?php

namespace App\Enums;

enum KnowledgeLinkRelation: string
{
    case Explains   = 'explains';
    case DependsOn  = 'depends_on';
    case Solves     = 'solves';
    case Contradicts = 'contradicts';
    case Updates    = 'updates';
    case SourceOf   = 'source_of';
    case ExampleOf  = 'example_of';
}
