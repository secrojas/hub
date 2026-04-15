# Guía Completa – Práctica 3 (DbText Widget)

Esta guía resume todos los pasos necesarios para implementar correctamente la práctica, siguiendo los patrones reales de Avature.

---

# 0. Crear rama

```bash
itasBranch autoschedule-dbtext
```

---

# 1. Frontend – Crear widget

## Archivo
```
autoschedule/UI/editor/fields/scheduleevent/DbText.js
```

## Implementación inicial (texto estático)

```javascript
import { TIN_display_DelayedWidget } from '/jscore/tinkerset/package/display/DelayedWidget.js';

class autoschedule_editor_fields_scheduleevent_DbText extends TIN_display_DelayedWidget {

    render() {
        return this.element('div', {}, [
            'This is some static text'
        ]);
    }
}

export { autoschedule_editor_fields_scheduleevent_DbText };
```

---

# 2. Insertar widget con LazyContainer

## Modificar
```
autoschedule/UI/editor/fields/scheduleevent/NoEvent.js
```

## Import
```javascript
import { autoschedule_editor_fields_scheduleevent_DbText } from './DbText.js';
import { TIN_display_LazyContainer } from '/jscore/tinkerset/package/display/LazyContainer.js';
```

## Uso
```javascript
new TIN_display_LazyContainer(
    'div',
    null,
    new autoschedule_editor_fields_scheduleevent_DbText()
)
```

---

# 3. Base de datos – SelfContained Updates

## Ruta
```
sharedTools/updates/selfContained/
```

---

## 3.1 Crear tabla

Archivo:
```
addAutoscheduleTextTable.sql
```

```sql
/**
* Creates autoschedule text table
*
* @owner("autoschedule")
* @failPolicy("stopReleaseAndContactDeveloper")
*/

CREATE TABLE autoscheduleText (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    text VARCHAR(255),
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '@keepNeeded';
```

---

## 3.2 Insertar dato

Archivo:
```
insertAutoscheduleText.sql
```

```sql
/**
* Inserts default autoschedule text
*
* @owner("autoschedule")
* @failPolicy("stopReleaseAndContactDeveloper")
*/

INSERT INTO autoscheduleText (text)
VALUES ('This is some static text from DB');
```

---

## 3.3 Registrar updates

Archivo:
```
sharedTools/updates/selfContainedList.txt
```

Agregar al final:
```
addAutoscheduleTextTable.sql
insertAutoscheduleText.sql
```

---

# 4. Backend – Estructura

## Crear carpetas
```
module/autoschedule/text/
module/autoschedule/text/persistence/
```

---

# 5. QueryBuilder

Archivo:
```
persistence/QueryBuilder.php
```

```php
class M_autoschedule_text_persistence_QueryBuilder {

    public const TABLE = 'autoscheduleText';

    public function get(): COR_sql_Select {
        $select = new COR_sql_Select(self::TABLE);
        $select->addField('text');
        $select->setLimit(1);
        return $select;
    }
}
```

---

# 6. Dao

Archivo:
```
persistence/Dao.php
```

```php
namespace Avature\MainApp\autoschedule\text\persistence;

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
        return $row['text'] ?? null;
    }
}
```

---

# 7. Service

Archivo:
```
Service.php
```

```php
use Avature\MainApp\autoschedule\text\persistence\Dao;
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
}
```

---

# 8. API

Archivo:
```
module/autoschedule/API/Text.php
```

```php
class M_autoschedule_API_Text {

    public function getText() {
        $service = M_autoschedule_text_Service::build();

        return [
            'text' => $service->getText()
        ];
    }
}
```

---

# 9. Frontend – Conectar con backend

## Reemplazar DbText.js

```javascript
import { TIN_display_DelayedWidget } from '/jscore/tinkerset/package/display/DelayedWidget.js';
import { ICO_harp_DataSource } from '/jscore/module/harp/UI/DataSource.js';

class autoschedule_editor_fields_scheduleevent_DbText extends TIN_display_DelayedWidget {

    prepare() {
        this.addDependencies([
            this.textDataSource = new ICO_harp_DataSource(
                'autoschedule_Text',
                'getText',
                []
            )
        ]);
    }

    render() {
        const response = this.textDataSource.get();

        return this.element('div', {}, [
            response ? response.text : ''
        ]);
    }
}

export { autoschedule_editor_fields_scheduleevent_DbText };
```

---

# 10. Flujo final

```
DB → QueryBuilder → Dao → Service → API → DataSource → Widget
```

---

# 11. Commit

```bash
git add .
git commit -m "ITIS | Add DbText widget with backend integration"
git push
```

---

# 12. Checklist final

- [ ] Widget renderiza texto estático
- [ ] LazyContainer implementado correctamente
- [ ] Tabla creada vía selfContained
- [ ] selfContainedList actualizado
- [ ] Dao usa QueryBuilder
- [ ] Service usa DI
- [ ] API responde correctamente
- [ ] DataSource funciona

---

# 13. Errores comunes

- No usar LazyContainer
- No registrar selfContainedList
- No usar QueryBuilder
- Llamar API manualmente en vez de DataSource
- Naming incorrecto de API

---

Con esto deberías poder completar la práctica sin bloqueos y pasar pipeline + code review.

