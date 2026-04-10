<?php

namespace App\Repositories;

use App\Contracts\Repositories\NoteRepositoryInterface;
use App\Models\Note;
use Illuminate\Database\Eloquent\Collection;

class NoteRepository implements NoteRepositoryInterface
{
    public function getAll(): Collection
    {
        return Note::with('folder')
            ->orderByDesc('esta_fijada')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function findById(int $id): Note
    {
        return Note::with('folder')->findOrFail($id);
    }

    public function create(array $data): Note
    {
        return Note::create($data);
    }

    public function update(Note $note, array $data): Note
    {
        $note->update($data);

        return $note->fresh('folder');
    }

    public function delete(Note $note): void
    {
        $note->delete();
    }

    public function search(string $term): Collection
    {
        return Note::with('folder')
            ->where('titulo', 'like', "%{$term}%")
            ->orWhere('extracto', 'like', "%{$term}%")
            ->orderByDesc('esta_fijada')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function getByFolder(?int $folderId): Collection
    {
        return Note::with('folder')
            ->where('folder_id', $folderId)
            ->orderByDesc('esta_fijada')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function getForDashboard(): Collection
    {
        return Note::with('folder')
            ->where('en_dashboard', true)
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();
    }
}
