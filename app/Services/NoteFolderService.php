<?php

namespace App\Services;

use App\Contracts\Repositories\NoteFolderRepositoryInterface;
use App\Models\NoteFolder;
use Illuminate\Database\Eloquent\Collection;

class NoteFolderService
{
    public function __construct(
        private readonly NoteFolderRepositoryInterface $repository,
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function create(array $data): NoteFolder
    {
        return $this->repository->create($data);
    }

    public function update(NoteFolder $folder, array $data): NoteFolder
    {
        return $this->repository->update($folder, $data);
    }

    public function delete(NoteFolder $folder): void
    {
        $this->repository->delete($folder);
    }
}
