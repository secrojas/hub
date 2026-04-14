<?php

namespace App\Models;

use App\Enums\KnowledgeLinkRelation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_entry_id',
        'to_entry_id',
        'relation_type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'relation_type' => KnowledgeLinkRelation::class,
        ];
    }

    public function fromEntry(): BelongsTo
    {
        return $this->belongsTo(KnowledgeEntry::class, 'from_entry_id');
    }

    public function toEntry(): BelongsTo
    {
        return $this->belongsTo(KnowledgeEntry::class, 'to_entry_id');
    }
}
