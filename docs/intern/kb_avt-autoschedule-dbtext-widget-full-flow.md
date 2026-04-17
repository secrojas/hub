---
entry_id: avt-autoschedule-dbtext-widget-full-flow
titulo: Flujo completo end-to-end en Avature — Widget TIN + API Harp + Service + DAO + DB (con bugs reales resueltos)
type: flow
confidence: high
domain: autoschedule
subdomain: ui-backend-integration
tags: autoschedule, TIN, DelayedWidget, LazyContainer, Peg, DataSource, Harp API, Service, DAO, QueryBuilder, DI, selfContained, PHP, JavaScript
scope: module
summary: Implementación completa de un widget Avature (DbText) que lee texto desde DB usando DelayedWidget + LazyContainer + Peg + ICO_harp_DataSource en frontend, y API Harp sin constructor DI + Service::build() + DAO + QueryBuilder en backend. Incluye selfContained DB updates y resolución de 8 bugs reales de DI, naming, lifecycle TIN y syntax PHP.
avature_version:
embedding_priority: high
---

## Qué es este flujo

Implementación de un widget Avature end-to-end que:
- Renderiza datos desde base de datos en la UI (módulo `autoschedule`)
- Usa el stack completo: TIN (DelayedWidget) → DataSource → Harp API → Service → DAO → QueryBuilder → DB
- Sirve como caso introductorio a cómo se trabaja en Avature con el stack legado/moderno

---

## Arquitectura / Flujo de datos

```
UI (DbText.js - DelayedWidget)
   ↓
TIN_display_LazyContainer + Peg  ← necesario para que el widget renderice dentro de otro widget
   ↓
ICO_harp_DataSource('autoschedule_Text', 'getText', [])
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
```

---

## Estructura de archivos

```
module/autoschedule/
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
```

---

## Código final por archivo

### DbText.js

```js
import { TIN_display_DelayedWidget } from '/jscore/tinkerset/package/display/DelayedWidget.js';
import { ICO_harp_DataSource } from '/jscore/module/harp/UI/DataSource.js';

class autoschedule_editor_fields_scheduleevent_DbText extends TIN_display_DelayedWidget {

    prepare() {
        this.addDependencies([
            this.textDataSource = new ICO_harp_DataSource(
                'autoschedule_Text',
                'getText',
                []           // ← OBLIGATORIO aunque esté vacío
            )
        ]);
    }

    render() {
        const data = this.textDataSource.get();

        return this.element('div', {}, [
            data && data.text ? data.text : 'No text available'
        ]);
    }
}

export { autoschedule_editor_fields_scheduleevent_DbText };
```

---

### NoEvent.js — integración del widget (fragmento)

```js
import { autoschedule_editor_fields_scheduleevent_DbText } from './DbText.js';
import { TIN_display_LazyContainer } from '/jscore/tinkerset/package/display/LazyContainer.js';

// Dentro de _getNoEventContent():
Peg(
    new TIN_display_LazyContainer(
        'div',
        {'class': css('NoEventInfo')},
        new autoschedule_editor_fields_scheduleevent_DbText()
    )
),
```

**Por qué `Peg()` es necesario**: Al insertar un widget dentro de `element(...)` de otro widget, TIN no gestiona bien el lifecycle si no está envuelto en `Peg`. Sin él, aparece `NotFoundError: removeChild` al re-renderizar.

---

### API — Text.php

```php
<?php

class M_autoschedule_API_Text extends M_ICO_harp_BaseApi {

    public function getText(): array {
        $service = M_autoschedule_text_Service::build();

        return [
            'text' => $service->getText()
        ];
    }
}
```

**Regla crítica**: Las clases API de Harp **NO usan constructor injection**. `ICO_harp_DataSource` instancia la clase como `new M_autoschedule_API_Text()` sin parámetros. Si usás DI en el constructor, `$service` queda `null` y el widget desaparece silenciosamente sin error visible.

---

### Service.php

```php
<?php

use Avature\MainApp\autoschedule\text\persistence\Dao;
use Avature\MainApp\Core\dependencyinjection\PLUG\Service as DIService;

class M_autoschedule_text_Service {

    private $dao;   // ← NO usar typed properties (private Dao $dao) en clases M_

    public function __construct(Dao $dao) {
        $this->dao = $dao;
    }

    public static function build(): M_autoschedule_text_Service {
        return new self(
            (new DIService())->getContainer()->get(Dao::class)
        );
    }

    public function getText(): ?string {
        return $this->dao->getText();
    }
}
```

**Regla**: Las clases con prefijo `M_` no usan namespace y no usan typed properties (`private Dao $dao` da ParseError en este estilo legado).

---

### Dao.php

```php
<?php

namespace Avature\MainApp\autoschedule\text\persistence;

use Avature\MainApp\db\PLUG\MasterQueryRunner;

class Dao {

    private $queryBuilder;
    private $db;

    public function __construct(
        QueryBuilder $queryBuilder,
        MasterQueryRunner $db
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->db = $db;
    }

    public function getText(): ?string {
        $row = $this->db->getOne($this->queryBuilder->get());
        return $row['text'] ?? null;
    }
}
```

---

### QueryBuilder.php

```php
<?php

namespace Avature\MainApp\autoschedule\text\persistence;

use COR_sql_Select;

class QueryBuilder {

    public const TABLE = 'autoscheduleText';
    public const DEFAULT_ID = 1;

    public function get(): COR_sql_Select {
        $select = new COR_sql_Select(self::TABLE);
        $select->addField('id');
        $select->addField('text');
        $select->addWhereFieldEquals('id', self::DEFAULT_ID);
        return $select;
    }
}
```

**Regla**: Archivos dentro de `persistence/` con namespace moderno usan nombre de clase limpio (`QueryBuilder`, `Dao`), **sin prefijo `M_`**. Mezclar namespace con `M_` rompe el autoloader del DI container.

---

## Base de datos

### selfContained/addAutoscheduleTextTable.sql

```sql
/**
* Creates autoschedule text table
*
* @owner("video")
* @failPolicy("stopReleaseAndContactDeveloper")
*/

CREATE TABLE autoscheduleText (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    text VARCHAR(255),
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '@keepNeeded';
```

### selfContained/insertAutoscheduleText.sql

```sql
/**
* Inserts default autoschedule text
*
* @owner("video")
* @failPolicy("stopReleaseAndContactDeveloper")
*/

INSERT INTO autoscheduleText (text)
VALUES ('This is some static text from DB');
```

### selfContainedList.txt — agregar al final

```
addAutoscheduleTextTable.sql
insertAutoscheduleText.sql
```

---

## Bugs reales encontrados y resueltos

### Bug 1 — Widget no renderizaba (DOM removeChild)
**Síntoma**: `NotFoundError: Failed to execute 'removeChild' on 'Node'` en `SquealerIndicator.js`.
**Causa**: El `LazyContainer` se insertó dentro de `element(...)` sin `Peg()`. TIN no pudo gestionar el lifecycle y rompió al intentar desmontar nodos.
**Fix**: Siempre envolver en `Peg(new TIN_display_LazyContainer(...))` cuando se inserta un widget dentro de la estructura de otro.

### Bug 2 — Widget desaparecía con DataSource activo
**Síntoma**: Con `prepare()` activo el widget desaparecía. Con `prepare()` vacío renderizaba bien.
**Causa raíz**: `ICO_harp_DataSource` no estaba resolviendo porque la API fallaba silenciosamente. En `DelayedWidget`, si alguna dependencia de `addDependencies()` falla, `render()` nunca se ejecuta.
**Técnica de debug**: Vaciar `prepare()` para aislar. Si renderiza → el problema está en el DataSource/API. Si tampoco renderiza → el problema está en la inserción del widget.

### Bug 3 — Tercer parámetro de DataSource faltante
**Síntoma**: Widget no renderizaba, error sutil en lifecycle.
**Causa**: `new ICO_harp_DataSource('autoschedule_Text', 'getText')` sin el tercer argumento.
**Fix**: El tercer parámetro es OBLIGATORIO aunque sea array vacío: `new ICO_harp_DataSource('autoschedule_Text', 'getText', [])`.

### Bug 4 — API con constructor DI (crítico, error silencioso)
**Síntoma**: Widget desaparecía, `$service` era `null`, sin error visible en logs.
**Causa**: Se usó `__construct(M_autoschedule_text_Service $service)`. Harp instancia las APIs sin parámetros, entonces el servicio quedaba `null`.
**Fix**: Las APIs Harp nunca usan constructor DI. Llamar a `Service::build()` dentro del método.

### Bug 5 — ParseError: typed property en clase M_
**Síntoma**: `ParseError: syntax error, unexpected 'Dao' (T_STRING)` en `Service.php`.
**Causa**: `private Dao $dao` — las typed properties PHP 7.4+ no son compatibles con clases legado sin namespace.
**Fix**: Usar `private $dao` sin tipo. El type hint en `__construct(Dao $dao)` sí es válido.

### Bug 6 — DI container no encuentra QueryBuilder
**Síntoma**: `No entry or class found for 'Avature\MainApp\autoschedule\text\persistence\M_autoschedule_text_persistence_QueryBuilder'`.
**Causa**: Se mezcló namespace moderno con nombre legado `M_autoschedule_text_persistence_QueryBuilder`. El DI container buscó ese nombre completo bajo el namespace y no lo encontró.
**Fix**: Si la clase tiene namespace, el nombre debe ser limpio (`QueryBuilder`, no `M_algo`). Nunca mezclar ambas convenciones.

### Bug 7 — @owner inválido en selfContained updates
**Síntoma**: `E_NOTICE: Undefined index: autoschedule` en `ModificationControlHelper.php`.
**Causa**: Se usó `@owner("autoschedule")` pero ese módulo no estaba registrado en el sistema de updates.
**Fix**: Usar un owner registrado como `"video"` o `"framework"`. Nunca inventar un owner nuevo.

### Bug 8 — selfContainedList.txt inconsistente
**Síntoma**: `E_WARNING: array_diff(): Argument #2 is not an array`.
**Causa**: El archivo `.sql` existía en `selfContained/` pero no estaba registrado en `selfContainedList.txt`, o había un typo/whitespace en el nombre.
**Fix**: El nombre en el `.txt` debe ser exactamente igual al del archivo (case sensitive, sin espacios extra). Si hubo error de encoding, reescribir la línea manualmente sin copiar/pegar.

---

## Reglas de oro Avature

| Regla | Detalle |
|-------|---------|
| Harp API sin constructor DI | `M_ICO_harp_BaseApi` es instanciada sin argumentos. Usar `Service::build()` dentro del método. |
| No mezclar `M_` con namespace | Si la clase tiene namespace → nombre limpio. Si tiene `M_` → sin namespace. |
| `private $dao` sin tipo en clases `M_` | Las typed properties rompen el parser en clases legado. |
| Tercer arg en DataSource obligatorio | `new ICO_harp_DataSource(entity, method, [])` — el array vacío es requerido. |
| `Peg()` al insertar widgets en `element()` | Cualquier widget que va dentro de la estructura DOM de otro necesita `Peg()`. |
| `LazyContainer` para `DelayedWidget` | Sin `LazyContainer` como wrapper, el `DelayedWidget` nunca renderiza. |
| `addDependencies()` bloquea `render()` | Si alguna dependencia falla, `render()` no se ejecuta. |
| `selfContainedList.txt` es el registry | Sin registrar el `.sql` en esa lista, el update no corre aunque el archivo exista. |
| `@owner` debe ser un módulo registrado | No inventar owners. Usar uno válido del sistema (`"video"`, `"framework"`, etc.). |

---

## Mapeo DataSource → API

```js
// JS:
new ICO_harp_DataSource('autoschedule_Text', 'getText', [])
```

```php
// PHP — nombre de clase resultante:
class M_autoschedule_API_Text {
    public function getText()
}
```

Regla: `autoschedule_Text` → `M_autoschedule_API_Text`. El segmento después del `_` se capitaliza.

---

## Técnica de debug recomendada

1. **Vaciar `prepare()`** y hardcodear texto en `render()` → ¿Se ve? Si sí, el problema está en el DataSource/API.
2. **Hardcodear la API** con `return ['text' => 'OK'];` → ¿Aparece? Si sí, el problema está en Service/DAO/DB.
3. **`console.log('DATA:', data)`** en `render()` → ¿Sale? Si no, `render()` no se está ejecutando (dependencia fallida).
4. **Texto rojo visible** para confirmar render path: `this.element('div', {style: 'color:red; border:2px solid red'}, ['TEST'])`.
