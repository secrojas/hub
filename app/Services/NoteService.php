<?php

namespace App\Services;

use App\Contracts\Repositories\NoteRepositoryInterface;
use App\Models\Note;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class NoteService
{
    public function __construct(
        private readonly NoteRepositoryInterface $repository,
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function findById(int $id): Note
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Note
    {
        $data['extracto'] = $this->generateExcerpt($data['contenido'] ?? '');

        return $this->repository->create($data);
    }

    public function update(Note $note, array $data): Note
    {
        $data['extracto'] = $this->generateExcerpt($data['contenido'] ?? '');

        return $this->repository->update($note, $data);
    }

    public function delete(Note $note): void
    {
        $this->repository->delete($note);
    }

    public function search(string $term): Collection
    {
        return $this->repository->search($term);
    }

    public function getByFolder(?int $folderId): Collection
    {
        return $this->repository->getByFolder($folderId);
    }

    public function getForDashboard(): Collection
    {
        return $this->repository->getForDashboard();
    }

    private function generateExcerpt(string $html): string
    {
        return Str::limit(strip_tags($html), 250);
    }
}
