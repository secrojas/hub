<?php

namespace App\Contracts\Repositories;

use App\Models\NoteFolder;
use Illuminate\Database\Eloquent\Collection;

interface NoteFolderRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): NoteFolder;

    public function create(array $data): NoteFolder;

    public function update(NoteFolder $folder, array $data): NoteFolder;

    public function delete(NoteFolder $folder): void;
}
