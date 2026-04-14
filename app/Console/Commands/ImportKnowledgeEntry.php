<?php

namespace App\Console\Commands;

use App\Enums\EmbeddingPriority;
use App\Enums\KnowledgeConfidence;
use App\Enums\KnowledgeSource;
use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeType;
use App\Models\KnowledgeEntry;
use App\Services\KnowledgeEntryService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportKnowledgeEntry extends Command
{
    protected $signature = 'knowledge:import {file : Ruta al archivo .txt con el template de la entrada}';
    protected $description = 'Importa una entrada al Knowledge Base desde un archivo template';

    public function __construct(private readonly KnowledgeEntryService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! file_exists($path)) {
            $this->error("Archivo no encontrado: {$path}");
            return self::FAILURE;
        }

        $raw = file_get_contents($path);

        if (! preg_match('/---KB-ENTRY-START---(.*?)---KB-ENTRY-END---/s', $raw, $entryMatch)) {
            $this->error('No se encontró el bloque ---KB-ENTRY-START--- ... ---KB-ENTRY-END---');
            return self::FAILURE;
        }

        $block = $entryMatch[1];

        // Extraer y convertir contenido Markdown → HTML
        $contenido = '';
        if (preg_match('/---CONTENIDO-START---(.*?)---CONTENIDO-END---/s', $block, $contentMatch)) {
            $contenido = Str::markdown(trim($contentMatch[1]));
            $block = preg_replace('/---CONTENIDO-START---.*?---CONTENIDO-END---/s', '', $block);
        }

        // Parsear campos metadata
        $meta = [];
        foreach (explode("\n", $block) as $line) {
            if (preg_match('/^(\w+):\s*(.*)$/', trim($line), $m)) {
                $meta[$m[1]] = trim($m[2]);
            }
        }

        // Validar campos requeridos
        foreach (['entry_id', 'titulo', 'type', 'confidence', 'domain', 'summary'] as $field) {
            if (empty($meta[$field])) {
                $this->error("Campo requerido faltante: {$field}");
                return self::FAILURE;
            }
        }

        if (KnowledgeEntry::where('entry_id', $meta['entry_id'])->exists()) {
            $this->error("Ya existe una entrada con entry_id '{$meta['entry_id']}'.");
            return self::FAILURE;
        }

        $tags = ! empty($meta['tags'])
            ? array_values(array_filter(array_map('trim', explode(',', $meta['tags']))))
            : [];

        $this->table(
            ['Campo', 'Valor'],
            [
                ['entry_id',   $meta['entry_id']],
                ['titulo',     $meta['titulo']],
                ['type',       $meta['type']],
                ['confidence', $meta['confidence']],
                ['domain',     $meta['domain']],
                ['subdomain',  $meta['subdomain'] ?? '—'],
                ['scope',      $meta['scope'] ?? '—'],
                ['tags',       implode(', ', $tags) ?: '—'],
                ['summary',    Str::limit($meta['summary'], 80)],
                ['contenido',  $contenido ? Str::limit(strip_tags($contenido), 60).'  [HTML generado]' : '—'],
            ]
        );

        if (! $this->confirm('¿Crear esta entrada?', true)) {
            $this->line('Cancelado.');
            return self::SUCCESS;
        }

        $entry = $this->service->create([
            'entry_id'           => $meta['entry_id'],
            'titulo'             => $meta['titulo'],
            'type'               => $meta['type'],
            'status'             => KnowledgeStatus::Draft->value,
            'confidence'         => $meta['confidence'],
            'source'             => KnowledgeSource::Chatgpt->value,
            'verified'           => false,
            'domain'             => $meta['domain'],
            'subdomain'          => $meta['subdomain'] ?? null,
            'tags'               => $tags,
            'scope'              => $meta['scope'] ?? null,
            'summary'            => $meta['summary'],
            'contenido'          => $contenido,
            'avature_version'    => $meta['avature_version'] ?? null,
            'embedding_priority' => $meta['embedding_priority'] ?? EmbeddingPriority::Normal->value,
        ]);

        $this->info("Entrada creada: {$entry->entry_id} (ID: {$entry->id})");
        $this->line("  → http://127.0.0.1:8000/knowledge/{$entry->id}");

        return self::SUCCESS;
    }
}
