<?php

namespace App\Models;

use App\Enums\EmbeddingPriority;
use App\Enums\KnowledgeConfidence;
use App\Enums\KnowledgeSource;
use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_id',
        'titulo',
        'type',
        'status',
        'confidence',
        'source',
        'verified',
        'domain',
        'subdomain',
        'tags',
        'scope',
        'summary',
        'contenido',
        'avature_version',
        'embedding_priority',
    ];

    protected function casts(): array
    {
        return [
            'type'               => KnowledgeType::class,
            'status'             => KnowledgeStatus::class,
            'confidence'         => KnowledgeConfidence::class,
            'source'             => KnowledgeSource::class,
            'embedding_priority' => EmbeddingPriority::class,
            'verified'           => 'boolean',
            'tags'               => 'array',
        ];
    }

    public function links(): HasMany
    {
        return $this->hasMany(KnowledgeLink::class, 'from_entry_id');
    }

    public function backlinks(): HasMany
    {
        return $this->hasMany(KnowledgeLink::class, 'to_entry_id');
    }
}
