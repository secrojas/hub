<?php

namespace App\Http\Requests;

use App\Enums\EmbeddingPriority;
use App\Enums\KnowledgeConfidence;
use App\Enums\KnowledgeSource;
use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKnowledgeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_id'           => ['required', 'string', 'max:50', Rule::unique('knowledge_entries', 'entry_id')->ignore($this->route('knowledge')), 'regex:/^[a-z0-9\-]+$/'],
            'titulo'             => ['required', 'string', 'max:255'],
            'type'               => ['required', Rule::enum(KnowledgeType::class)],
            'status'             => ['required', Rule::enum(KnowledgeStatus::class)],
            'confidence'         => ['required', Rule::enum(KnowledgeConfidence::class)],
            'source'             => ['required', Rule::enum(KnowledgeSource::class)],
            'verified'           => ['boolean'],
            'domain'             => ['nullable', 'string', 'max:100'],
            'subdomain'          => ['nullable', 'string', 'max:100'],
            'tags'               => ['nullable', 'array'],
            'tags.*'             => ['string'],
            'scope'              => ['nullable', 'string', 'in:module,system,cross-system'],
            'summary'            => ['nullable', 'string', 'max:500'],
            'contenido'          => ['nullable', 'string'],
            'avature_version'    => ['nullable', 'string', 'max:100'],
            'embedding_priority' => ['required', Rule::enum(EmbeddingPriority::class)],
        ];
    }
}
