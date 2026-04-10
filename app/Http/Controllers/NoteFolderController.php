<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteFolderRequest;
use App\Http\Requests\UpdateNoteFolderRequest;
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

    public function update(UpdateNoteFolderRequest $request, NoteFolder $noteFolder): RedirectResponse
    {
        $this->folderService->update($noteFolder, $request->validated());

        return redirect()->back()->with('success', 'Carpeta actualizada.');
    }

    public function destroy(NoteFolder $noteFolder): RedirectResponse
    {
        $this->folderService->delete($noteFolder);

        return redirect()->route('notes.index')->with('success', 'Carpeta eliminada.');
    }
}
