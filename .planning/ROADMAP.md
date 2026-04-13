# Roadmap: Hub

## Milestones

- ✅ **v1.0 MVP** — Phases 1–7 (shipped 2026-04-01)
- 🔄 **v1.1 Knowledge Base** — Phase 1 (in progress)

## Phases

<details>
<summary>✅ v1.0 MVP (Phases 1–7) — SHIPPED 2026-04-01</summary>

- [x] Phase 1: Fundacion y Auth (4/4 plans) — completed 2026-03-25
- [x] Phase 2: CRM de Clientes (3/3 plans) — completed 2026-03-25
- [x] Phase 3: Tareas y Kanban (3/3 plans) — completed 2026-03-20
- [x] Phase 4: Facturacion (3/3 plans) — completed 2026-03-25
- [x] Phase 5: Presupuestos y PDF (3/3 plans) — completed 2026-03-30
- [x] Phase 6: Portal del Cliente (2/2 plans) — completed 2026-03-31
- [x] Phase 7: Dashboard del Admin (2/2 plans) — completed 2026-03-31

Full details: `.planning/milestones/v1.0-ROADMAP.md`

</details>

### Phase 1: Knowledge Base Module

**Goal:** Implementar un módulo `/knowledge` completamente separado del módulo de notas existente, orientado a documentar conocimiento técnico de trabajo en empresa. Incluye modelo `KnowledgeEntry` con schema rico (id estable, type, status, confidence, source, domain, subdomain, summary, avature_version, embedding_priority), tabla `knowledge_links` con tipos de relación semántica, patrón Repository/Service, y UI completa con CRUD en `/knowledge`. El módulo de notas existente NO debe ser modificado.
**Requirements**: REQ-KB-1, REQ-KB-2, REQ-KB-3, REQ-KB-4, REQ-KB-5, REQ-KB-6, REQ-KB-7
**Depends on:** None
**Plans:** 4 plans

Plans:
- [ ] 01-01-PLAN.md — Foundation: migrations, enums, and Eloquent models (KnowledgeEntry + KnowledgeLink)
- [ ] 01-02-PLAN.md — Repository/Service layer with interface, implementation, and AppServiceProvider binding
- [ ] 01-03-PLAN.md — HTTP layer: FormRequests, Controllers, and route registration
- [ ] 01-04-PLAN.md — Vue UI: Index (filters), Create, Show (links management), Edit pages + AdminLayout nav link

## Progress

| Phase | Milestone | Plans Complete | Status | Completed |
|-------|-----------|----------------|--------|-----------|
| 1. Knowledge Base Module | v1.1 | 0/4 | In Progress | — |
