<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Services\NoteFolderService;
use App\Services\NoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService,
        private readonly NoteFolderService $folderService,
    ) {}

    public function index(Request $request): Response
    {
        $notes = match (true) {
            $request->filled('search')    => $this->noteService->search($request->search),
            $request->filled('folder_id') => $this->noteService->getByFolder((int) $request->folder_id),
            default                        => $this->noteService->getAll(),
        };

        return Inertia::render('Admin/Notes/Index', [
            'notes'   => $notes,
            'folders' => $this->folderService->getAll(),
            'filters' => $request->only(['search', 'folder_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Notes/Create', [
            'folders' => $this->folderService->getAll(),
        ]);
    }

    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $note = $this->noteService->create($request->validated());

        return redirect()->route('notes.show', $note)->with('success', 'Nota creada.');
    }

    public function show(Note $note): Response
    {
        return Inertia::render('Admin/Notes/Show', [
            'note'    => $note->load('folder'),
            'folders' => $this->folderService->getAll(),
        ]);
    }

    public function edit(Note $note): Response
    {
        return Inertia::render('Admin/Notes/Edit', [
            'note'    => $note->load('folder'),
            'folders' => $this->folderService->getAll(),
        ]);
    }

    public function update(UpdateNoteRequest $request, Note $note): RedirectResponse
    {
        $this->noteService->update($note, $request->validated());

        return redirect()->route('notes.show', $note)->with('success', 'Nota actualizada.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        $this->noteService->delete($note);

        return redirect()->route('notes.index')->with('success', 'Nota eliminada.');
    }
}
