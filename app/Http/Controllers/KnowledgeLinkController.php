<?php

namespace App\Http\Controllers;

use App\Enums\KnowledgeLinkRelation;
use App\Models\KnowledgeEntry;
use App\Models\KnowledgeLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KnowledgeLinkController extends Controller
{
    public function store(Request $request, KnowledgeEntry $knowledge): RedirectResponse
    {
        $request->validate([
            'to_entry_id'   => ['required', 'exists:knowledge_entries,id'],
            'relation_type' => ['required', Rule::enum(KnowledgeLinkRelation::class)],
            'notes'         => ['nullable', 'string'],
        ]);

        $knowledge->links()->create($request->only(['to_entry_id', 'relation_type', 'notes']));

        return redirect()->back()->with('success', 'Relación creada.');
    }

    public function destroy(KnowledgeLink $knowledgeLink): RedirectResponse
    {
        $knowledgeLink->delete();

        return redirect()->back()->with('success', 'Relación eliminada.');
    }
}
