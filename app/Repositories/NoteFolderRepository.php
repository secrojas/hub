<?php

namespace App\Repositories;

use App\Contracts\Repositories\NoteFolderRepositoryInterface;
use App\Models\NoteFolder;
use Illuminate\Database\Eloquent\Collection;

class NoteFolderRepository implements NoteFolderRepositoryInterface
{
    public function getAll(): Collection
    {
        return NoteFolder::withCount('notes')
            ->orderBy('nombre')
            ->get();
    }

    public function findById(int $id): NoteFolder
    {
        return NoteFolder::findOrFail($id);
    }

    public function create(array $data): NoteFolder
    {
        return NoteFolder::create($data);
    }

    public function delete(NoteFolder $folder): void
    {
        $folder->delete();
    }
}
