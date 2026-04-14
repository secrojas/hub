# Knowledge System Design
## Personal Engineering Memory — AI-Augmented Knowledge Base

> Arquitectura, templates, estrategia de retrieval y evolución del sistema de notas del hub.
> Generado: 2026-04-10

---

## Diagnóstico general

El sistema tiene buena dirección pero tiene **tres problemas estructurales** que van a doler a medida que escale:

1. **Una plantilla genérica para tipos de conocimiento fundamentalmente distintos** — un bug y un concepto no tienen la misma forma. Forzarlos al mismo template genera ruido.
2. **No hay relaciones entre notas** — es un archivo plano, no un grafo. El conocimiento real es reticular, no jerárquico.
3. **No está optimizado para retrieval** — "AI-ready" requiere más que Markdown bien estructurado.

---

## 1. Schema del frontmatter

### Schema actual (insuficiente)
```yaml
title, type, tags, created_at
```

### Schema propuesto
```yaml
---
# Identidad estable
id: "avt-rtc-001"           # CRÍTICO: ID human-readable, permanente, no auto-increment
title: "RTC — Videoconference Creation Flow"
type: concept | flow | bug | decision | runbook | glossary

# Estado del conocimiento
status: draft | reviewed | verified | stale
confidence: low | medium | high    # qué tan seguro estás de esto
source: chatgpt | self | docs | colleague
verified: false                    # corroborado en código real

# Clasificación
domain: "video"                    # módulo principal de Avature
subdomain: "rtc"                   # subsistema específico
tags: ["webrtc", "rtctevent", "signaling"]
scope: module | system | cross-system

# Temporal
created_at: 2026-04-10
updated_at: 2026-04-10
avature_version: "sprint-Q1-2026"  # para detectar staleness

# AI-readiness
summary: "El módulo RTC maneja señalización WebRTC via rtctevent. El principal failure mode es que la videoconferencia no se crea si el hook no se dispara."
embedding_priority: high | normal

# Relaciones
references:
  - id: "avt-rtc-core"
    relation: depends_on
  - id: "avt-signaling-flow"
    relation: explains
  - id: "avt-ticket-042"
    relation: source_of
context_pack:
  - "avt-rtc-core"
  - "avt-media-server"
---
```

### Campos críticos explicados

| Campo | Por qué importa |
|-------|----------------|
| `id` estable | Si usás auto-increment y migrás datos, todos los links se rompen |
| `summary` | El "retrieval anchor" — una oración densa para embeddings y búsqueda BM25 |
| `status` | Diferencia conocimiento verificado de especulación |
| `confidence` | El LLM no debe sobre-confiar en notas `low` |
| `avature_version` | Permite detectar staleness cuando el sistema evoluciona |
| `verified` | Notas de ChatGPT nunca salen de `false` hasta que vos las validás |

---

## 2. Templates por tipo

### `type: concept`
```markdown
---
[frontmatter]
type: concept
---

## Definición
Una sola oración. Sin jerga innecesaria.

## Modelo mental
Analogía o metáfora para entender el concepto rápido.

## Cómo funciona
Explicación técnica. Pseudocódigo si es necesario.

## Propiedades clave
- Propiedad con implicancia concreta
- Propiedad con implicancia concreta

## Misconceptions comunes
- **Mito**: X hace Y
  **Realidad**: X hace Z porque...

## Relaciones con otros componentes
- Se conecta con [avt-rtc-core] via...

## Open Questions
- Duda pendiente de validar
```

---

### `type: flow`
```markdown
---
[frontmatter]
type: flow
---

## Trigger
Qué dispara este flujo. Desde dónde.

## Precondiciones
Qué debe ser verdad para que inicie.

## Pasos
1. [ComponenteA] hace X
2. [ComponenteB] recibe Y y decide Z
3. Si condición → camino A | Si no → camino B

## Estado final exitoso
Qué debe ser verdad al terminar.

## Failure modes
| Punto de fallo | Consecuencia | Señal observable |
|----------------|--------------|-----------------|
| Hook no se dispara | Videoconf no creada | Sin log en rtctevent |

## Paths de código (abstraído)
Module > Class > Method (sin código propietario)

## Open Questions
```

---

### `type: bug`
```markdown
---
[frontmatter]
type: bug
---

## Síntoma
Qué se observó. Desde la perspectiva del usuario.

## Root Cause
Por qué pasó. La causa real, no el síntoma.

## Componentes afectados
- Componente A — rol en el bug
- Componente B — rol en el bug

## Fix aplicado
Qué se hizo. Abstracto, sin código propietario.

## Cómo detectarlo antes
Señales tempranas. Qué monitorear.

## Prevention
Cambio de patrón o verificación para evitar recurrencia.

## Pasos de reproducción
1. ...
2. ...

## Tickets relacionados
- AVT-XXXX
```

---

### `type: decision`
```markdown
---
[frontmatter]
type: decision
---

## Contexto
Qué problema se estaba resolviendo. Por qué importaba.

## Opciones consideradas
| Opción | Pros | Contras |
|--------|------|---------|
| A | ... | ... |
| B | ... | ... |

## Decisión tomada
Qué se eligió.

## Rationale
Por qué esta opción sobre las demás.

## Trade-offs aceptados
Qué se sacrificó conscientemente.

## Consecuencias
Qué implica esto hacia adelante.
```

---

### `type: runbook`
```markdown
---
[frontmatter]
type: runbook
---

## Cuándo usar este runbook
Trigger exacto. Sin ambigüedad.

## Precondiciones
Accesos, estados, contexto necesario.

## Pasos
1. Verificar X
2. Si Y → hacer Z
3. Confirmar estado final

## Verificación
Cómo saber que funcionó.

## Rollback
Qué hacer si salió mal.
```

---

### `type: glossary`
```markdown
---
[frontmatter]
type: glossary
---

## Definición formal
La definición exacta en el contexto de Avature.

## En mis palabras
Cómo lo entiendo yo.

## Confundible con
Términos similares y cómo diferenciarlos.

## Usado en
- [avt-rtc-flow] — cómo aparece en ese contexto
```

---

## 3. Relaciones entre notas — el grafo

### Tabla en DB: `note_links`

```sql
CREATE TABLE note_links (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_note_id    BIGINT UNSIGNED NOT NULL,
    to_note_id      BIGINT UNSIGNED NOT NULL,
    relation_type   ENUM(
                        'explains',    -- A explica B
                        'depends_on',  -- A necesita entender B primero
                        'solves',      -- A resuelve el problema de B
                        'contradicts', -- A dice algo diferente a B
                        'updates',     -- A reemplaza/corrige B
                        'source_of',   -- A es la fuente primaria de B
                        'example_of'   -- A es ejemplo concreto de B
                    ) NOT NULL,
    notes           TEXT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (to_note_id)   REFERENCES notes(id) ON DELETE CASCADE
);
```

### Tipos de relación explicados

| Relación | Semántica | Uso típico |
|----------|-----------|------------|
| `explains` | A da más detalle sobre B | Concepto → Subconcepto |
| `depends_on` | Para entender A hay que leer B antes | Flow → Concepto base |
| `solves` | A resuelve el problema descrito en B | Fix → Bug |
| `contradicts` | A y B dicen cosas incompatibles | Cuando encontrás info conflictiva |
| `updates` | A corrige o reemplaza B | Cuando Avature cambia algo |
| `source_of` | A es la fuente que generó B | Ticket → Notas derivadas |
| `example_of` | A es instancia concreta de B | Caso real → Concepto general |

### Lo que esto habilita

- **Grafo visual** — ver cómo se conecta el conocimiento
- **Contexto automático** — al abrir una nota, sugerir las `depends_on`
- **Staleness cascade** — si A se marca `stale`, alertar todo lo que `depends_on` A
- **Context pack para AI** — "dame A + todo lo que A depende" como bloque de contexto

---

## 4. AI-Readiness

### Estrategia de chunking

Los embeddings no funcionan bien sobre notas completas largas. Cada sección (H2) es un chunk independiente.

```sql
CREATE TABLE note_chunks (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    note_id     BIGINT UNSIGNED NOT NULL,
    section     VARCHAR(100),   -- "Flow", "Root Cause", "Cómo funciona", etc.
    content     TEXT,
    embedding   JSON,           -- float[] almacenado como JSON
    tokens      INT,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
);
```

Cuando alguien busca "cómo funciona el signaling en RTC", solo la sección `## Flow` es relevante — no la nota entera. Chunking por sección es chunking natural.

### Context injection estratificado

Cuando le pasás contexto a un LLM, tres capas:

```
CAPA 1 — Siempre presente (embedding_priority: high)
  Los 3-5 nodos raíz del dominio actual.
  Ej: "Avature Architecture Overview", "Video System — Mental Model"
  → Se incluyen en TODOS los prompts sobre ese dominio.

CAPA 2 — Por query semántico (cosine similarity sobre summaries)
  Las 5-8 notas más relevantes a la pregunta concreta.
  → Recuperadas en tiempo real antes de enviar el prompt.

CAPA 3 — Por grafo (traversal de depends_on)
  Los prerequisitos de las notas en Capa 2.
  → Sin esto, Claude tiene la respuesta pero no el contexto para razonar bien.
```

### Implementación de búsqueda — por etapas

| Etapa | Qué implementás | Cuándo |
|-------|----------------|--------|
| 1 | FULLTEXT MySQL sobre campo `summary` | Hoy. 80% del valor. |
| 2 | Embeddings en JSON + cosine en PHP | A los 100+ notas |
| 3 | SQLite con sqlite-vec (vector DB local) | A los 300+ notas |

No necesitás pgvector ni Chroma para empezar. BM25 sobre `summary` fields bien escritos te lleva lejos.

### Campo AI Context Block (opcional, avanzado)

Para notas críticas, un bloque comprimido especial para inyección en LLM:

```html
<!-- ai-context
summary: RTC module handles WebRTC signaling via rtctevent. Main failure mode is videoconference not created when hook not fired. Key components: rtctevent orchestrator, rcs-setting, media server.
cluster: video-system
priority: high
-->
```

Este bloque no se muestra al usuario pero es lo que se inyecta PRIMERO en el contexto del LLM antes de la nota completa.

---

## 5. Evaluación del pipeline ChatGPT → Hub → Claude

### Fortalezas
- ChatGPT como primer intérprete del conocimiento corporativo: correcto. Tiene acceso al contexto del corporate.
- Hub como formato neutral: correcto. No estar atado a ningún proveedor.
- Claude para síntesis y razonamiento: buen fit.

### Los tres problemas reales

#### Problema 1: ChatGPT genera, vos no verificás
El output de ChatGPT sobre sistemas internos tiene errores. Si lo guardás sin verificar, tu knowledge base tiene ruido confiante — lo peor que puede pasar.

**Regla**: Nunca guardes una nota de ChatGPT con `status: verified`. Solo `draft`. Solo vos (al validarlo contra el sistema real) podés promoverla.

#### Problema 2: No hay prompt template estándar para ChatGPT
Si cada vez que preguntás el output tiene forma distinta, convertirlo a tu schema es trabajo manual. Esto genera fricción y gaps.

**Solución — Prompt de sistema fijo para ChatGPT:**
```
Sos un asistente técnico que documenta conocimiento sobre sistemas complejos.
Cuando expliques algo, usá SIEMPRE este formato:

## Definición
## Cómo funciona
## Componentes involucrados
## Failure modes
## Open questions

Usá pseudocódigo, nunca código propietario.
Sé denso y técnico. Evitá padding.
Al final, generá un campo "summary" de una sola oración densa
que capture la esencia del concepto para búsqueda.
```

Con esto, el copy-paste al hub es casi directo.

#### Problema 3: No hay feedback loop
Cuando usás una nota para resolver un problema, esa aplicación no se registra. En 6 meses no sabés qué notas fueron útiles, cuáles te llevaron por mal camino.

**Solución**: Campo `usage_count` + mini log de aplicación. No tiene que ser sofisticado — solo existe.

---

## 6. Evolución a largo plazo

### Qué va a romper primero (en orden)

| Volumen | Qué rompe | Solución |
|---------|-----------|----------|
| ~100 notas | Discoverability | Búsqueda full-text sobre `summary` |
| ~200 notas | Staleness | `avature_version` + proceso de review periódico |
| ~500 notas | Redundancia y contradicción | `canonical: true` + `relation: updates` |
| ~1000 notas | Context window | Chunking + embeddings + retrieval selectivo |

### Lo que tenés que diseñar HOY

| Problema futuro | Implementás ahora |
|----------------|------------------|
| Discoverability | Campo `summary` denso en cada nota |
| Staleness | Campo `avature_version` + `status: stale` |
| Redundancia | IDs estables (`avt-rtc-001`) + `relation: updates` |
| Context window | Secciones como unidades independientes (chunks naturales) |
| Contradicción | Campo `confidence` + `verified` |

---

## 7. Roadmap de implementación

### Prioridad 1 — Esta semana (sin escribir código)
- [ ] Definir y adoptar el schema completo del frontmatter
- [ ] Crear prompt de sistema fijo para ChatGPT
- [ ] Reescribir las notas existentes con el nuevo schema (especialmente `summary` e `id`)

### Prioridad 2 — Próximo sprint (backend)
- [ ] Agregar campos al modelo `Note`: `note_id` (slug), `summary`, `status`, `confidence`, `verified`, `source`, `domain`, `subdomain`, `avature_version`, `embedding_priority`
- [ ] Crear tabla `note_links` con tipos de relación
- [ ] UI para crear links entre notas
- [ ] Búsqueda full-text sobre campo `summary`

### Prioridad 3 — Cuando llegués a 100+ notas
- [ ] Tabla `note_chunks` (chunking por sección)
- [ ] Generación de embeddings via API (Claude o OpenAI)
- [ ] Búsqueda semántica (cosine similarity en PHP o SQLite-vec)
- [ ] "Context pack generator" — seleccionás un tema, el hub genera el prompt optimizado

### Prioridad 4 — Largo plazo
- [ ] Grafo visual de notas (vis.js o similar)
- [ ] Staleness cascade alerts
- [ ] Workflow de review periódico (notas con `avature_version` viejo se alertan)

---

## Resumen ejecutivo

El sistema es correcto en dirección. Los cambios en orden de impacto:

1. **`summary` denso en cada nota** — el ROI más alto. Mejora búsqueda Y context injection.
2. **IDs estables** — sin esto el grafo no escala.
3. **Templates por tipo** — menos ruido, más señal.
4. **`note_links`** — transforma el sistema de archivo plano a knowledge graph.
5. **Prompt fijo para ChatGPT** — baja la fricción de captura.
6. **Búsqueda full-text sobre `summary`** — discoverability real.
7. **Embeddings + chunking** — cuando el volumen lo justifique.

El pipeline ChatGPT → Hub → Claude es sólido. El problema no es el pipeline sino la calidad y estructura de lo que entra. En un knowledge base de AI, el garbage se amplifica — una nota incorrecta con `confidence: high` es peor que no tenerla.
