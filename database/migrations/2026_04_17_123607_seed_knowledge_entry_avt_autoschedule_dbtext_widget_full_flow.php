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
                'titulo'             => 'Flujo completo end-to-end en Avature — Widget TIN + API Harp + Service + DAO + DB (con bugs reales resueltos)',
                'type'               => 'flow',
                'status'             => 'draft',
                'confidence'         => 'high',
                'source'             => 'chatgpt',
                'verified'           => false,
                'domain'             => 'autoschedule',
                'subdomain'          => 'ui-backend-integration',
                'tags'               => '["autoschedule","TIN","DelayedWidget","LazyContainer","Peg","DataSource","Harp API","Service","DAO","QueryBuilder","DI","selfContained","PHP","JavaScript"]',
                'scope'              => 'module',
                'summary'            => 'Implementación completa de un widget Avature (DbText) que lee texto desde DB usando DelayedWidget + LazyContainer + Peg + ICO_harp_DataSource en frontend, y API Harp sin constructor DI + Service::build() + DAO + QueryBuilder en backend. Incluye selfContained DB updates y resolución de 8 bugs reales de DI, naming, lifecycle TIN y syntax PHP.',
                'contenido'          => '<h2>Qué es este flujo</h2>
<p>Implementación de un widget Avature end-to-end que:</p>
<ul>
<li>Renderiza datos desde base de datos en la UI (módulo <code>autoschedule</code>)</li>
<li>Usa el stack completo: TIN (DelayedWidget) → DataSource → Harp API → Service → DAO → QueryBuilder → DB</li>
<li>Sirve como caso introductorio a cómo se trabaja en Avature con el stack legado/moderno</li>
</ul>
<hr />
<h2>Arquitectura / Flujo de datos</h2>
<pre><code>UI (DbText.js - DelayedWidget)
   ↓
TIN_display_LazyContainer + Peg  ← necesario para que el widget renderice dentro de otro widget
   ↓
ICO_harp_DataSource(\'autoschedule_Text\', \'getText\', [])
   ↓
M_autoschedule_API_Text::getText()   ← NO usa constructor DI (crítico)
   ↓
M_autoschedule_text_Service::build()  ← usa DI container
   ↓
Dao::getText()
   ↓
QueryBuilder::get()   ← namespace moderno, clase sin prefijo M_
   ↓
MasterQueryRunner::getOne()
   ↓
DB: tabla autoscheduleText
</code></pre>
<hr />
<h2>Estructura de archivos</h2>
<pre><code>module/autoschedule/
  UI/editor/fields/scheduleevent/
    DbText.js                   ← nuevo widget
    NoEvent.js                  ← modificado (integra DbText)

  API/
    Text.php                    ← nuevo endpoint Harp

  text/
    Service.php                 ← sin namespace (clase M_)
    persistence/
      Dao.php                   ← con namespace moderno
      QueryBuilder.php          ← con namespace moderno

  initialdata/
    db.dump.sql                 ← CREATE TABLE agregado
    db.data.sql                 ← INSERT agregado

sharedTools/updates/selfContained/
  addAutoscheduleTextTable.sql
  insertAutoscheduleText.sql

sharedTools/updates/
  selfContainedList.txt         ← registrar ambos archivos acá
</code></pre>
<hr />
<h2>Código final por archivo</h2>
<h3>DbText.js</h3>
<pre><code class="language-js">import { TIN_display_DelayedWidget } from \'/jscore/tinkerset/package/display/DelayedWidget.js\';
import { ICO_harp_DataSource } from \'/jscore/module/harp/UI/DataSource.js\';

class autoschedule_editor_fields_scheduleevent_DbText extends TIN_display_DelayedWidget {

    prepare() {
        this.addDependencies([
            this.textDataSource = new ICO_harp_DataSource(
                \'autoschedule_Text\',
                \'getText\',
                []           // ← OBLIGATORIO aunque esté vacío
            )
        ]);
    }

    render() {
        const data = this.textDataSource.get();

        return this.element(\'div\', {}, [
            data &amp;&amp; data.text ? data.text : \'No text available\'
        ]);
    }
}

export { autoschedule_editor_fields_scheduleevent_DbText };
</code></pre>
<hr />
<h3>NoEvent.js — integración del widget (fragmento)</h3>
<pre><code class="language-js">import { autoschedule_editor_fields_scheduleevent_DbText } from \'./DbText.js\';
import { TIN_display_LazyContainer } from \'/jscore/tinkerset/package/display/LazyContainer.js\';

// Dentro de _getNoEventContent():
Peg(
    new TIN_display_LazyContainer(
        \'div\',
        {\'class\': css(\'NoEventInfo\')},
        new autoschedule_editor_fields_scheduleevent_DbText()
    )
),
</code></pre>
<p><strong>Por qué <code>Peg()</code> es necesario</strong>: Al insertar un widget dentro de <code>element(...)</code> de otro widget, TIN no gestiona bien el lifecycle si no está envuelto en <code>Peg</code>. Sin él, aparece <code>NotFoundError: removeChild</code> al re-renderizar.</p>
<hr />
<h3>API — Text.php</h3>
<pre><code class="language-php">&lt;?php

class M_autoschedule_API_Text extends M_ICO_harp_BaseApi {

    public function getText(): array {
        $service = M_autoschedule_text_Service::build();

        return [
            \'text\' =&gt; $service-&gt;getText()
        ];
    }
}
</code></pre>
<p><strong>Regla crítica</strong>: Las clases API de Harp <strong>NO usan constructor injection</strong>. <code>ICO_harp_DataSource</code> instancia la clase como <code>new M_autoschedule_API_Text()</code> sin parámetros. Si usás DI en el constructor, <code>$service</code> queda <code>null</code> y el widget desaparece silenciosamente sin error visible.</p>
<hr />
<h3>Service.php</h3>
<pre><code class="language-php">&lt;?php

use Avature\\MainApp\\autoschedule\\text\\persistence\\Dao;
use Avature\\MainApp\\Core\\dependencyinjection\\PLUG\\Service as DIService;

class M_autoschedule_text_Service {

    private $dao;   // ← NO usar typed properties (private Dao $dao) en clases M_

    public function __construct(Dao $dao) {
        $this-&gt;dao = $dao;
    }

    public static function build(): M_autoschedule_text_Service {
        return new self(
            (new DIService())-&gt;getContainer()-&gt;get(Dao::class)
        );
    }

    public function getText(): ?string {
        return $this-&gt;dao-&gt;getText();
    }
}
</code></pre>
<p><strong>Regla</strong>: Las clases con prefijo <code>M_</code> no usan namespace y no usan typed properties (<code>private Dao $dao</code> da ParseError en este estilo legado).</p>
<hr />
<h3>Dao.php</h3>
<pre><code class="language-php">&lt;?php

namespace Avature\\MainApp\\autoschedule\\text\\persistence;

use Avature\\MainApp\\db\\PLUG\\MasterQueryRunner;

class Dao {

    private $queryBuilder;
    private $db;

    public function __construct(
        QueryBuilder $queryBuilder,
        MasterQueryRunner $db
    ) {
        $this-&gt;queryBuilder = $queryBuilder;
        $this-&gt;db = $db;
    }

    public function getText(): ?string {
        $row = $this-&gt;db-&gt;getOne($this-&gt;queryBuilder-&gt;get());
        return $row[\'text\'] ?? null;
    }
}
</code></pre>
<hr />
<h3>QueryBuilder.php</h3>
<pre><code class="language-php">&lt;?php

namespace Avature\\MainApp\\autoschedule\\text\\persistence;

use COR_sql_Select;

class QueryBuilder {

    public const TABLE = \'autoscheduleText\';
    public const DEFAULT_ID = 1;

    public function get(): COR_sql_Select {
        $select = new COR_sql_Select(self::TABLE);
        $select-&gt;addField(\'id\');
        $select-&gt;addField(\'text\');
        $select-&gt;addWhereFieldEquals(\'id\', self::DEFAULT_ID);
        return $select;
    }
}
</code></pre>
<p><strong>Regla</strong>: Archivos dentro de <code>persistence/</code> con namespace moderno usan nombre de clase limpio (<code>QueryBuilder</code>, <code>Dao</code>), <strong>sin prefijo <code>M_</code></strong>. Mezclar namespace con <code>M_</code> rompe el autoloader del DI container.</p>
<hr />
<h2>Base de datos</h2>
<h3>selfContained/addAutoscheduleTextTable.sql</h3>
<pre><code class="language-sql">/**
* Creates autoschedule text table
*
* @owner(&quot;video&quot;)
* @failPolicy(&quot;stopReleaseAndContactDeveloper&quot;)
*/

CREATE TABLE autoscheduleText (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    text VARCHAR(255),
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = \'@keepNeeded\';
</code></pre>
<h3>selfContained/insertAutoscheduleText.sql</h3>
<pre><code class="language-sql">/**
* Inserts default autoschedule text
*
* @owner(&quot;video&quot;)
* @failPolicy(&quot;stopReleaseAndContactDeveloper&quot;)
*/

INSERT INTO autoscheduleText (text)
VALUES (\'This is some static text from DB\');
</code></pre>
<h3>selfContainedList.txt — agregar al final</h3>
<pre><code>addAutoscheduleTextTable.sql
insertAutoscheduleText.sql
</code></pre>
<hr />
<h2>Bugs reales encontrados y resueltos</h2>
<h3>Bug 1 — Widget no renderizaba (DOM removeChild)</h3>
<p><strong>Síntoma</strong>: <code>NotFoundError: Failed to execute \'removeChild\' on \'Node\'</code> en <code>SquealerIndicator.js</code>.
<strong>Causa</strong>: El <code>LazyContainer</code> se insertó dentro de <code>element(...)</code> sin <code>Peg()</code>. TIN no pudo gestionar el lifecycle y rompió al intentar desmontar nodos.
<strong>Fix</strong>: Siempre envolver en <code>Peg(new TIN_display_LazyContainer(...))</code> cuando se inserta un widget dentro de la estructura de otro.</p>
<h3>Bug 2 — Widget desaparecía con DataSource activo</h3>
<p><strong>Síntoma</strong>: Con <code>prepare()</code> activo el widget desaparecía. Con <code>prepare()</code> vacío renderizaba bien.
<strong>Causa raíz</strong>: <code>ICO_harp_DataSource</code> no estaba resolviendo porque la API fallaba silenciosamente. En <code>DelayedWidget</code>, si alguna dependencia de <code>addDependencies()</code> falla, <code>render()</code> nunca se ejecuta.
<strong>Técnica de debug</strong>: Vaciar <code>prepare()</code> para aislar. Si renderiza → el problema está en el DataSource/API. Si tampoco renderiza → el problema está en la inserción del widget.</p>
<h3>Bug 3 — Tercer parámetro de DataSource faltante</h3>
<p><strong>Síntoma</strong>: Widget no renderizaba, error sutil en lifecycle.
<strong>Causa</strong>: <code>new ICO_harp_DataSource(\'autoschedule_Text\', \'getText\')</code> sin el tercer argumento.
<strong>Fix</strong>: El tercer parámetro es OBLIGATORIO aunque sea array vacío: <code>new ICO_harp_DataSource(\'autoschedule_Text\', \'getText\', [])</code>.</p>
<h3>Bug 4 — API con constructor DI (crítico, error silencioso)</h3>
<p><strong>Síntoma</strong>: Widget desaparecía, <code>$service</code> era <code>null</code>, sin error visible en logs.
<strong>Causa</strong>: Se usó <code>__construct(M_autoschedule_text_Service $service)</code>. Harp instancia las APIs sin parámetros, entonces el servicio quedaba <code>null</code>.
<strong>Fix</strong>: Las APIs Harp nunca usan constructor DI. Llamar a <code>Service::build()</code> dentro del método.</p>
<h3>Bug 5 — ParseError: typed property en clase M_</h3>
<p><strong>Síntoma</strong>: <code>ParseError: syntax error, unexpected \'Dao\' (T_STRING)</code> en <code>Service.php</code>.
<strong>Causa</strong>: <code>private Dao $dao</code> — las typed properties PHP 7.4+ no son compatibles con clases legado sin namespace.
<strong>Fix</strong>: Usar <code>private $dao</code> sin tipo. El type hint en <code>__construct(Dao $dao)</code> sí es válido.</p>
<h3>Bug 6 — DI container no encuentra QueryBuilder</h3>
<p><strong>Síntoma</strong>: <code>No entry or class found for \'Avature\\MainApp\\autoschedule\\text\\persistence\\M_autoschedule_text_persistence_QueryBuilder\'</code>.
<strong>Causa</strong>: Se mezcló namespace moderno con nombre legado <code>M_autoschedule_text_persistence_QueryBuilder</code>. El DI container buscó ese nombre completo bajo el namespace y no lo encontró.
<strong>Fix</strong>: Si la clase tiene namespace, el nombre debe ser limpio (<code>QueryBuilder</code>, no <code>M_algo</code>). Nunca mezclar ambas convenciones.</p>
<h3>Bug 7 — @owner inválido en selfContained updates</h3>
<p><strong>Síntoma</strong>: <code>E_NOTICE: Undefined index: autoschedule</code> en <code>ModificationControlHelper.php</code>.
<strong>Causa</strong>: Se usó <code>@owner(&quot;autoschedule&quot;)</code> pero ese módulo no estaba registrado en el sistema de updates.
<strong>Fix</strong>: Usar un owner registrado como <code>&quot;video&quot;</code> o <code>&quot;framework&quot;</code>. Nunca inventar un owner nuevo.</p>
<h3>Bug 8 — selfContainedList.txt inconsistente</h3>
<p><strong>Síntoma</strong>: <code>E_WARNING: array_diff(): Argument #2 is not an array</code>.
<strong>Causa</strong>: El archivo <code>.sql</code> existía en <code>selfContained/</code> pero no estaba registrado en <code>selfContainedList.txt</code>, o había un typo/whitespace en el nombre.
<strong>Fix</strong>: El nombre en el <code>.txt</code> debe ser exactamente igual al del archivo (case sensitive, sin espacios extra). Si hubo error de encoding, reescribir la línea manualmente sin copiar/pegar.</p>
<hr />
<h2>Reglas de oro Avature</h2>
<table>
<thead>
<tr>
<th>Regla</th>
<th>Detalle</th>
</tr>
</thead>
<tbody>
<tr>
<td>Harp API sin constructor DI</td>
<td><code>M_ICO_harp_BaseApi</code> es instanciada sin argumentos. Usar <code>Service::build()</code> dentro del método.</td>
</tr>
<tr>
<td>No mezclar <code>M_</code> con namespace</td>
<td>Si la clase tiene namespace → nombre limpio. Si tiene <code>M_</code> → sin namespace.</td>
</tr>
<tr>
<td><code>private $dao</code> sin tipo en clases <code>M_</code></td>
<td>Las typed properties rompen el parser en clases legado.</td>
</tr>
<tr>
<td>Tercer arg en DataSource obligatorio</td>
<td><code>new ICO_harp_DataSource(entity, method, [])</code> — el array vacío es requerido.</td>
</tr>
<tr>
<td><code>Peg()</code> al insertar widgets en <code>element()</code></td>
<td>Cualquier widget que va dentro de la estructura DOM de otro necesita <code>Peg()</code>.</td>
</tr>
<tr>
<td><code>LazyContainer</code> para <code>DelayedWidget</code></td>
<td>Sin <code>LazyContainer</code> como wrapper, el <code>DelayedWidget</code> nunca renderiza.</td>
</tr>
<tr>
<td><code>addDependencies()</code> bloquea <code>render()</code></td>
<td>Si alguna dependencia falla, <code>render()</code> no se ejecuta.</td>
</tr>
<tr>
<td><code>selfContainedList.txt</code> es el registry</td>
<td>Sin registrar el <code>.sql</code> en esa lista, el update no corre aunque el archivo exista.</td>
</tr>
<tr>
<td><code>@owner</code> debe ser un módulo registrado</td>
<td>No inventar owners. Usar uno válido del sistema (<code>&quot;video&quot;</code>, <code>&quot;framework&quot;</code>, etc.).</td>
</tr>
</tbody>
</table>
<hr />
<h2>Mapeo DataSource → API</h2>
<pre><code class="language-js">// JS:
new ICO_harp_DataSource(\'autoschedule_Text\', \'getText\', [])
</code></pre>
<pre><code class="language-php">// PHP — nombre de clase resultante:
class M_autoschedule_API_Text {
    public function getText()
}
</code></pre>
<p>Regla: <code>autoschedule_Text</code> → <code>M_autoschedule_API_Text</code>. El segmento después del <code>_</code> se capitaliza.</p>
<hr />
<h2>Técnica de debug recomendada</h2>
<ol>
<li><strong>Vaciar <code>prepare()</code></strong> y hardcodear texto en <code>render()</code> → ¿Se ve? Si sí, el problema está en el DataSource/API.</li>
<li><strong>Hardcodear la API</strong> con <code>return [\'text\' =&gt; \'OK\'];</code> → ¿Aparece? Si sí, el problema está en Service/DAO/DB.</li>
<li><strong><code>console.log(\'DATA:\', data)</code></strong> en <code>render()</code> → ¿Sale? Si no, <code>render()</code> no se está ejecutando (dependencia fallida).</li>
<li><strong>Texto rojo visible</strong> para confirmar render path: <code>this.element(\'div\', {style: \'color:red; border:2px solid red\'}, [\'TEST\'])</code>.</li>
</ol>
',
                'avature_version'    => '',
                'embedding_priority' => 'high',
                'created_at'         => now(),
                'updated_at'         => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('knowledge_entries')->where('entry_id', 'avt-autoschedule-dbtext-widget-full-flow')->delete();
    }
};