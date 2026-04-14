<?php

namespace App\Services;

use App\Contracts\Repositories\KnowledgeEntryRepositoryInterface;
use App\Models\KnowledgeEntry;
use Illuminate\Database\Eloquent\Collection;

class KnowledgeEntryService
{
    public function __construct(
        private readonly KnowledgeEntryRepositoryInterface $repository,
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function findById(int $id): KnowledgeEntry
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): KnowledgeEntry
    {
        return $this->repository->create($data);
    }

    public function update(KnowledgeEntry $entry, array $data): KnowledgeEntry
    {
        return $this->repository->update($entry, $data);
    }

    public function delete(KnowledgeEntry $entry): void
    {
        $this->repository->delete($entry);
    }

    public function search(string $term): Collection
    {
        return $this->repository->search($term);
    }

    public function filterBy(array $filters): Collection
    {
        return $this->repository->filterBy($filters);
    }
}
