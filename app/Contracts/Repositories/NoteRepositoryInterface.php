<?php

namespace App\Contracts\Repositories;

use App\Models\Note;
use Illuminate\Database\Eloquent\Collection;

interface NoteRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): Note;

    public function create(array $data): Note;

    public function update(Note $note, array $data): Note;

    public function delete(Note $note): void;

    public function search(string $term): Collection;

    public function getByFolder(?int $folderId): Collection;
}
