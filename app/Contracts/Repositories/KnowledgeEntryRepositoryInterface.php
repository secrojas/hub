<?php

namespace App\Contracts\Repositories;

use App\Models\KnowledgeEntry;
use Illuminate\Database\Eloquent\Collection;

interface KnowledgeEntryRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): KnowledgeEntry;
    public function create(array $data): KnowledgeEntry;
    public function update(KnowledgeEntry $entry, array $data): KnowledgeEntry;
    public function delete(KnowledgeEntry $entry): void;
    public function search(string $term): Collection;
    public function filterBy(array $filters): Collection;
}
