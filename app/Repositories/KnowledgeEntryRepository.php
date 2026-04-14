<?php

namespace App\Repositories;

use App\Contracts\Repositories\KnowledgeEntryRepositoryInterface;
use App\Models\KnowledgeEntry;
use Illuminate\Database\Eloquent\Collection;

class KnowledgeEntryRepository implements KnowledgeEntryRepositoryInterface
{
    public function getAll(): Collection
    {
        return KnowledgeEntry::orderByDesc('updated_at')->get();
    }

    public function findById(int $id): KnowledgeEntry
    {
        return KnowledgeEntry::with(['links.toEntry', 'backlinks.fromEntry'])->findOrFail($id);
    }

    public function create(array $data): KnowledgeEntry
    {
        return KnowledgeEntry::create($data);
    }

    public function update(KnowledgeEntry $entry, array $data): KnowledgeEntry
    {
        $entry->update($data);

        return $entry->fresh();
    }

    public function delete(KnowledgeEntry $entry): void
    {
        $entry->delete();
    }

    public function search(string $term): Collection
    {
        return KnowledgeEntry::where('titulo', 'like', "%{$term}%")
            ->orWhere('summary', 'like', "%{$term}%")
            ->orderByDesc('updated_at')
            ->get();
    }

    public function filterBy(array $filters): Collection
    {
        $query = KnowledgeEntry::query();

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('titulo', 'like', "%{$term}%")
                    ->orWhere('summary', 'like', "%{$term}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['domain'])) {
            $query->where('domain', $filters['domain']);
        }

        return $query->orderByDesc('updated_at')->get();
    }
}
