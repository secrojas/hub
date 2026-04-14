<?php

namespace App\Http\Controllers;

use App\Enums\EmbeddingPriority;
use App\Enums\KnowledgeConfidence;
use App\Enums\KnowledgeLinkRelation;
use App\Enums\KnowledgeSource;
use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeType;
use App\Http\Requests\StoreKnowledgeEntryRequest;
use App\Http\Requests\UpdateKnowledgeEntryRequest;
use App\Models\KnowledgeEntry;
use App\Services\KnowledgeEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeEntryController extends Controller
{
    public function __construct(
        private readonly KnowledgeEntryService $service,
    ) {}

    public function index(Request $request): Response
    {
        $entries = $this->service->filterBy($request->only(['search', 'type', 'status', 'domain']));

        return Inertia::render('Admin/Knowledge/Index', [
            'entries'     => $entries,
            'filters'     => $request->only(['search', 'type', 'status', 'domain']),
            'types'       => collect(KnowledgeType::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'statuses'    => collect(KnowledgeStatus::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'confidences' => collect(KnowledgeConfidence::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'sources'     => collect(KnowledgeSource::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Knowledge/Create', [
            'types'               => collect(KnowledgeType::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'statuses'            => collect(KnowledgeStatus::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'confidences'         => collect(KnowledgeConfidence::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'sources'             => collect(KnowledgeSource::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'embeddingPriorities' => collect(EmbeddingPriority::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
        ]);
    }

    public function store(StoreKnowledgeEntryRequest $request): RedirectResponse
    {
        $entry = $this->service->create($request->validated());

        return redirect()->route('knowledge.show', $entry)->with('success', 'Entrada creada.');
    }

    public function show(KnowledgeEntry $knowledge): Response
    {
        $knowledge->load(['links.toEntry', 'backlinks.fromEntry']);

        return Inertia::render('Admin/Knowledge/Show', [
            'entry'         => $knowledge,
            'allEntries'    => $this->service->getAll(),
            'relationTypes' => collect(KnowledgeLinkRelation::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
        ]);
    }

    public function edit(KnowledgeEntry $knowledge): Response
    {
        return Inertia::render('Admin/Knowledge/Edit', [
            'entry'               => $knowledge,
            'types'               => collect(KnowledgeType::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'statuses'            => collect(KnowledgeStatus::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'confidences'         => collect(KnowledgeConfidence::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'sources'             => collect(KnowledgeSource::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
            'embeddingPriorities' => collect(EmbeddingPriority::cases())->map(fn ($c) => ['label' => $c->name, 'value' => $c->value]),
        ]);
    }

    public function update(UpdateKnowledgeEntryRequest $request, KnowledgeEntry $knowledge): RedirectResponse
    {
        $this->service->update($knowledge, $request->validated());

        return redirect()->route('knowledge.show', $knowledge)->with('success', 'Entrada actualizada.');
    }

    public function destroy(KnowledgeEntry $knowledge): RedirectResponse
    {
        $this->service->delete($knowledge);

        return redirect()->route('knowledge.index')->with('success', 'Entrada eliminada.');
    }
}
