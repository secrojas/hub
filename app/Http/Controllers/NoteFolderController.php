<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteFolderRequest;
use App\Models\NoteFolder;
use App\Services\NoteFolderService;
use Illuminate\Http\RedirectResponse;

class NoteFolderController extends Controller
{
    public function __construct(
        private readonly NoteFolderService $folderService,
    ) {}

    public function store(StoreNoteFolderRequest $request): RedirectResponse
    {
        $this->folderService->create($request->validated());

        return redirect()->route('notes.index')->with('success', 'Carpeta creada.');
    }

    public function destroy(NoteFolder $noteFolder): RedirectResponse
    {
        $this->folderService->delete($noteFolder);

        return redirect()->route('notes.index')->with('success', 'Carpeta eliminada.');
    }
}
