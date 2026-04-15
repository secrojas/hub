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
    protected $signature = 'knowledge:import {file : Ruta al archivo .md (con frontmatter) o .txt (template legacy)}';
    protected $description = 'Importa una entrada al Knowledge Base y genera la migración de producción';

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

        $raw       = file_get_contents($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        [$meta, $contenido] = $extension === 'md'
            ? $this->parseMarkdown($raw)
            : $this->parseTemplate($raw);

        if ($meta === null) {
            return self::FAILURE;
        }

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

        $migrationFile = $this->generateMigration($meta, $contenido, $tags);
        $this->line("  → Migración generada: database/migrations/{$migrationFile}");

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Parsers
    // -------------------------------------------------------------------------

    /**
     * Parsea un .md con frontmatter YAML estándar:
     *
     *   ---
     *   entry_id: avt-mi-entrada-001
     *   titulo:   Título de la entrada
     *   type:     flow
     *   domain:   iats
     *   summary:  Resumen breve...
     *   ---
     *
     *   # Contenido markdown aquí...
     */
    private function parseMarkdown(string $raw): array
    {
        if (! preg_match('/^---\r?\n(.*?)\r?\n---\r?\n(.*)/s', $raw, $match)) {
            $this->error('El archivo .md debe comenzar con frontmatter YAML.');
            $this->line('');
            $this->line('Formato esperado:');
            $this->line('---');
            $this->line('entry_id:   avt-mi-entrada-001');
            $this->line('titulo:     Título de la entrada');
            $this->line('type:       flow');
            $this->line('domain:     iats');
            $this->line('subdomain:  badge');
            $this->line('scope:      module');
            $this->line('confidence: high');
            $this->line('tags:       tag1, tag2, tag3');
            $this->line('summary:    Resumen breve de la entrada.');
            $this->line('---');
            $this->line('');
            $this->line('# Contenido del artículo...');
            return [null, null];
        }

        $meta = [];
        foreach (explode("\n", $match[1]) as $line) {
            if (preg_match('/^(\w+):\s*(.*)$/', trim($line), $m)) {
                $meta[$m[1]] = trim($m[2]);
            }
        }

        $contenido = Str::markdown(trim($match[2]));

        return [$meta, $contenido];
    }

    /**
     * Parsea el formato legacy .txt con bloques KB-ENTRY-START / CONTENIDO-START.
     */
    private function parseTemplate(string $raw): array
    {
        if (! preg_match('/---KB-ENTRY-START---(.*?)---KB-ENTRY-END---/s', $raw, $entryMatch)) {
            $this->error('No se encontró el bloque ---KB-ENTRY-START--- ... ---KB-ENTRY-END---');
            return [null, null];
        }

        $block     = $entryMatch[1];
        $contenido = '';

        if (preg_match('/---CONTENIDO-START---(.*?)---CONTENIDO-END---/s', $block, $contentMatch)) {
            $contenido = Str::markdown(trim($contentMatch[1]));
            $block     = preg_replace('/---CONTENIDO-START---.*?---CONTENIDO-END---/s', '', $block);
        }

        $meta = [];
        foreach (explode("\n", $block) as $line) {
            if (preg_match('/^(\w+):\s*(.*)$/', trim($line), $m)) {
                $meta[$m[1]] = trim($m[2]);
            }
        }

        return [$meta, $contenido];
    }

    // -------------------------------------------------------------------------
    // Migration generator
    // -------------------------------------------------------------------------

    private function generateMigration(array $meta, string $contenido, array $tags): string
    {
        $date     = now()->format('Y_m_d_His');
        $slug     = str_replace('-', '_', $meta['entry_id']);
        $filename = "{$date}_seed_knowledge_entry_{$slug}.php";
        $filepath = database_path("migrations/{$filename}");

        $entryId           = var_export($meta['entry_id'], true);
        $titulo            = var_export($meta['titulo'], true);
        $type              = var_export($meta['type'], true);
        $confidence        = var_export($meta['confidence'], true);
        $domain            = var_export($meta['domain'], true);
        $subdomain         = var_export($meta['subdomain'] ?? null, true);
        $tagsJson          = var_export(json_encode($tags), true);
        $scope             = var_export($meta['scope'] ?? null, true);
        $summary           = var_export($meta['summary'], true);
        $contenidoExported = var_export($contenido, true);
        $avatureVersion    = var_export($meta['avature_version'] ?? null, true);
        $embeddingPriority = var_export($meta['embedding_priority'] ?? 'normal', true);

        $php = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('knowledge_entries')->updateOrInsert(
            ['entry_id' => {$entryId}],
            [
                'titulo'             => {$titulo},
                'type'               => {$type},
                'status'             => 'draft',
                'confidence'         => {$confidence},
                'source'             => 'chatgpt',
                'verified'           => false,
                'domain'             => {$domain},
                'subdomain'          => {$subdomain},
                'tags'               => {$tagsJson},
                'scope'              => {$scope},
                'summary'            => {$summary},
                'contenido'          => {$contenidoExported},
                'avature_version'    => {$avatureVersion},
                'embedding_priority' => {$embeddingPriority},
                'created_at'         => now(),
                'updated_at'         => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('knowledge_entries')->where('entry_id', {$entryId})->delete();
    }
};
PHP;

        file_put_contents($filepath, $php);

        return $filename;
    }
}
