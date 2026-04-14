<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('knowledge_entries')->where('entry_id', 'avt-badge-001')->exists()) {
            return;
        }

        DB::table('knowledge_entries')->insert([
            'entry_id'           => 'avt-badge-001',
            'titulo'             => 'IATS — Feature Badge: Flujo completo de implementación',
            'type'               => 'flow',
            'status'             => 'draft',
            'confidence'         => 'medium',
            'source'             => 'chatgpt',
            'verified'           => false,
            'domain'             => 'iats',
            'subdomain'          => 'badge',
            'tags'               => json_encode([]),
            'scope'              => 'module',
            'summary'            => 'Implementar un módulo completo para listar y crear badges, entendiendo el flujo real de Avature (UI → Backend → DB).',
            'contenido'          => '<h2>Objetivo</h2><p>Implementar un módulo completo de Badges para: listar badges, crear badges, persistir en base de datos y entender el flujo completo UI ↔ Backend. Esta guía representa cómo se trabaja realmente en Avature.</p><h2>Arquitectura base</h2><p>El flujo estándar en TODO el sistema IATS:</p><pre><code>UI (JS Widget)
   ↓
Datasource (Harp)
   ↓
API (PHP - BaseView / BaseApi)
   ↓
PLUG (lógica)
   ↓
DAO (queries)
   ↓
DB</code></pre><h2>Estructura del módulo</h2><pre><code>module/badge/
├── UI/
│   ├── Module.js
│   ├── Module.ini
│   ├── Editor.js
│   ├── Editor.ini
│   ├── CreateMenu.js
│   ├── CreateMenu.ini
│   ├── Badge.js
│   └── Badge.css
│
├── API/
│   ├── BadgeSearcher.php
│   └── BadgeApi.php
│
├── MODEL/
│   └── Badge.php
│
├── PLUG/
│   └── BadgePlug.php
│
├── BadgeDao.php
│
├── initialdata/
│   └── db.dump.sql
│
├── CONTRACTTEST/
│   └── ...
│
└── test/</code></pre><h2>UI (Frontend)</h2><p><strong>Entry points</strong> en Module.js conectan URL → Widget. Cada archivo JS que usa <code>pkg.translate()</code> necesita su <code>.ini</code> correspondiente.</p><p><strong>Datasource:</strong></p><ul><li><p>Mock local: <code>TIN_datasource_Local</code></p></li><li><p>Backend real: <code>ICO_harp_DataSource(\'badge_BadgeSearcher\', \'get\')</code></p></li></ul><p><strong>Widgets del framework:</strong> <code>TIN_display_Widget</code>, <code>TIN_display_DelayedWidget</code>, <code>TIN_form_SaverBar</code>, <code>FormWidget</code>, <code>ResultList</code></p><h2>Backend</h2><p><strong>Flujo crear badge:</strong> UI Form → SaverBar → BadgeApi::save() → BadgePlug → DAO::insert() → DB</p><p><strong>DB:</strong> tabla <code>badge (id INT AUTO_INCREMENT, name VARCHAR(255), img VARCHAR(255))</code> en <code>initialdata/db.dump.sql</code></p><h2>Packs (MUY IMPORTANTE)</h2><p>Si no agregás el módulo a los packs, la UI no carga y los widgets no existen.</p><ul><li><p><code>code/resources/packs/packs-template.ini</code></p></li><li><p><code>code/resources/packs/packs.iats.ini</code></p></li></ul><h2>Failure modes comunes</h2><ul><li><p><strong>Cannot read properties of undefined (reading \'text\')</strong>: falta el <code>.ini</code> o key inexistente</p></li><li><p><strong>invalidLanguageId</strong>: DB mal inicializada o rebuild fallido (Behat)</p></li><li><p><strong>UI no carga</strong>: módulo no agregado a packs</p></li><li><p><strong>Widget no aparece</strong>: path mal definido en Module.js</p></li><li><p><strong>Backend no responde</strong>: API mal nombrada o datasource mal configurado</p></li></ul><h2>Reglas de oro</h2><ol><li><p>Siempre usar la cadena API → PLUG → DAO — nunca saltear capas</p></li><li><p>UI nunca accede directo a DB — siempre via Datasource → API</p></li><li><p>Cada widget JS que usa traducciones necesita su <code>.ini</code></p></li><li><p>Seguir naming conventions estrictas — el 80% de errores son nombres incorrectos, archivos mal ubicados o traducciones faltantes</p></li></ol><p></p>',
            'avature_version'    => null,
            'embedding_priority' => 'high',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('knowledge_entries')->where('entry_id', 'avt-badge-001')->delete();
    }
};
