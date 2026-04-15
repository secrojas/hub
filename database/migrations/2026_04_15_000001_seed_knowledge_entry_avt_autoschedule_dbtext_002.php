<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('knowledge_entries')->updateOrInsert(
            ['entry_id' => 'avt-autoschedule-dbtext-widget-full-flow'],
            [
                'titulo'             => 'ITIS — DbText Widget: Flujo completo Frontend + Backend + DB',
                'type'               => 'flow',
                'status'             => 'draft',
                'confidence'         => 'high',
                'source'             => 'chatgpt',
                'verified'           => false,
                'domain'             => 'iats',
                'subdomain'          => 'autoschedule',
                'tags'               => json_encode([
                    'autoschedule',
                    'TIN',
                    'datasource',
                    'backend',
                    'selfcontained',
                    'widget',
                    'DelayedWidget',
                    'LazyContainer',
                    'QueryBuilder',
                    'Dao',
                    'Service',
                ]),
                'scope'              => 'module',
                'summary'            => 'Flujo completo para implementar un DbText widget (TIN_display_DelayedWidget) en Avature que renderiza texto desde base de datos. Cubre frontend con LazyContainer, SelfContained Updates para DB, y la cadena completa QueryBuilder → Dao → Service → API → DataSource → Widget.',
                'contenido'          => '<h2>0. Crear rama</h2><pre><code>itasBranch autoschedule-dbtext</code></pre><h2>1. Frontend – Crear widget</h2><p><strong>Archivo:</strong> <code>autoschedule/UI/editor/fields/scheduleevent/DbText.js</code></p><h3>Implementación inicial (texto estático)</h3><pre><code class="language-javascript">import { TIN_display_DelayedWidget } from \'/jscore/tinkerset/package/display/DelayedWidget.js\';

class autoschedule_editor_fields_scheduleevent_DbText extends TIN_display_DelayedWidget {

    render() {
        return this.element(\'div\', {}, [
            \'This is some static text\'
        ]);
    }
}

export { autoschedule_editor_fields_scheduleevent_DbText };</code></pre><h2>2. Insertar widget con LazyContainer</h2><p><strong>Modificar:</strong> <code>autoschedule/UI/editor/fields/scheduleevent/NoEvent.js</code></p><h3>Import</h3><pre><code class="language-javascript">import { autoschedule_editor_fields_scheduleevent_DbText } from \'./DbText.js\';
import { TIN_display_LazyContainer } from \'/jscore/tinkerset/package/display/LazyContainer.js\';</code></pre><h3>Uso</h3><pre><code class="language-javascript">new TIN_display_LazyContainer(
    \'div\',
    null,
    new autoschedule_editor_fields_scheduleevent_DbText()
)</code></pre><h2>3. Base de datos – SelfContained Updates</h2><p><strong>Ruta base:</strong> <code>sharedTools/updates/selfContained/</code></p><h3>3.1 Crear tabla</h3><p><strong>Archivo:</strong> <code>addAutoscheduleTextTable.sql</code></p><pre><code class="language-sql">/**
* Creates autoschedule text table
*
* @owner("autoschedule")
* @failPolicy("stopReleaseAndContactDeveloper")
*/

CREATE TABLE autoscheduleText (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    text VARCHAR(255),
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = \'@keepNeeded\';</code></pre><h3>3.2 Insertar dato</h3><p><strong>Archivo:</strong> <code>insertAutoscheduleText.sql</code></p><pre><code class="language-sql">/**
* Inserts default autoschedule text
*
* @owner("autoschedule")
* @failPolicy("stopReleaseAndContactDeveloper")
*/

INSERT INTO autoscheduleText (text)
VALUES (\'This is some static text from DB\');</code></pre><h3>3.3 Registrar updates</h3><p><strong>Archivo:</strong> <code>sharedTools/updates/selfContainedList.txt</code> — agregar al final:</p><pre><code>addAutoscheduleTextTable.sql
insertAutoscheduleText.sql</code></pre><h2>4. Backend – Estructura de carpetas</h2><pre><code>module/autoschedule/text/
module/autoschedule/text/persistence/</code></pre><h2>5. QueryBuilder</h2><p><strong>Archivo:</strong> <code>persistence/QueryBuilder.php</code></p><pre><code class="language-php">class M_autoschedule_text_persistence_QueryBuilder {

    public const TABLE = \'autoscheduleText\';

    public function get(): COR_sql_Select {
        $select = new COR_sql_Select(self::TABLE);
        $select->addField(\'text\');
        $select->setLimit(1);
        return $select;
    }
}</code></pre><h2>6. Dao</h2><p><strong>Archivo:</strong> <code>persistence/Dao.php</code></p><pre><code class="language-php">namespace Avature\MainApp\autoschedule\text\persistence;

use Avature\MainApp\db\PLUG\MasterQueryRunner;

class Dao {

    private $queryBuilder;
    private $db;

    public function __construct(
        M_autoschedule_text_persistence_QueryBuilder $queryBuilder,
        MasterQueryRunner $db
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->db = $db;
    }

    public function getText(): ?string {
        $row = $this->db->getOne($this->queryBuilder->get());
        return $row[\'text\'] ?? null;
    }
}</code></pre><h2>7. Service</h2><p><strong>Archivo:</strong> <code>Service.php</code></p><pre><code class="language-php">use Avature\MainApp\autoschedule\text\persistence\Dao;
use Avature\MainApp\Core\dependencyinjection\PLUG\Service as DIService;

class M_autoschedule_text_Service {

    private $dao;

    public function __construct(Dao $dao) {
        $this->dao = $dao;
    }

    public static function build(): self {
        return new self(
            (new DIService())->getContainer()->get(Dao::class)
        );
    }

    public function getText(): ?string {
        return $this->dao->getText();
    }
}</code></pre><h2>8. API</h2><p><strong>Archivo:</strong> <code>module/autoschedule/API/Text.php</code></p><pre><code class="language-php">class M_autoschedule_API_Text {

    public function getText() {
        $service = M_autoschedule_text_Service::build();

        return [
            \'text\' => $service->getText()
        ];
    }
}</code></pre><h2>9. Frontend – Conectar con backend</h2><p>Reemplazar el contenido de <code>DbText.js</code> con la versión que usa DataSource:</p><pre><code class="language-javascript">import { TIN_display_DelayedWidget } from \'/jscore/tinkerset/package/display/DelayedWidget.js\';
import { ICO_harp_DataSource } from \'/jscore/module/harp/UI/DataSource.js\';

class autoschedule_editor_fields_scheduleevent_DbText extends TIN_display_DelayedWidget {

    prepare() {
        this.addDependencies([
            this.textDataSource = new ICO_harp_DataSource(
                \'autoschedule_Text\',
                \'getText\',
                []
            )
        ]);
    }

    render() {
        const response = this.textDataSource.get();

        return this.element(\'div\', {}, [
            response ? response.text : \'\'
        ]);
    }
}

export { autoschedule_editor_fields_scheduleevent_DbText };</code></pre><h2>10. Flujo final</h2><pre><code>DB → QueryBuilder → Dao → Service → API → DataSource → Widget</code></pre><h2>11. Commit</h2><pre><code class="language-bash">git add .
git commit -m "ITIS | Add DbText widget with backend integration"
git push</code></pre><h2>12. Checklist final</h2><ul><li>Widget renderiza texto estático</li><li>LazyContainer implementado correctamente</li><li>Tabla creada vía selfContained</li><li>selfContainedList actualizado</li><li>Dao usa QueryBuilder</li><li>Service usa DI</li><li>API responde correctamente</li><li>DataSource funciona</li></ul><h2>13. Errores comunes</h2><ul><li>No usar <code>LazyContainer</code> — el widget no carga</li><li>No registrar en <code>selfContainedList.txt</code> — la tabla no se crea</li><li>No usar <code>QueryBuilder</code> — query directo en Dao es antipatrón</li><li>Llamar API manualmente en vez de usar <code>DataSource</code></li><li>Naming incorrecto de API — el DataSource no la encuentra</li></ul>',
                'avature_version'    => null,
                'embedding_priority' => 'high',
                'updated_at'         => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('knowledge_entries')->where('entry_id', 'avt-autoschedule-dbtext-widget-full-flow')->delete();
    }
};
