# MVP API de Viáticos en Laravel

> Índice funcional y técnico para Codex + Laravel Boost.
>
> Repositorio: `api-viaticos`
> Framework: Laravel 13 / PHP 8.3+
> Tipo de aplicación: API REST

## Objetivo

Construir un MVP de viáticos que cubra el flujo completo desde la solicitud previa del colaborador hasta la autorización de la comprobación por Finanzas, dejando puntos de extensión para OCR, CFDI, PDF y workflows automatizados.

Este archivo es un **índice**. El detalle se divide por etapas para que el agente implemente una parte a la vez.

## Perfiles

| Perfil | Responsabilidad |
|---|---|
| `COLABORADOR` | Crea, firma y envía solicitudes y comprobaciones. |
| `FINANZAS` | Revisa, autoriza o rechaza solicitudes y comprobaciones. |

## Reglas invariables

1. Toda comprobación debe pertenecer a una solicitud previamente `APROBADA`.
2. No existe comprobación de reembolso sin solicitud previa.
3. Una solicitud puede tener **N comprobaciones**.
4. Finanzas autoriza solicitudes y comprobaciones.
5. Solicitudes y comprobaciones requieren firma electrónica interna al enviarse y aprobarse.
6. Los estados cambian por acciones explícitas, nunca por CRUD genérico.
7. El backend es fuente de verdad para usuarios, roles, estados, totales, límites, excedentes y firmas.
8. OCR, n8n, PDF y proveedores externos no se llaman directamente desde Controllers.
9. El MVP es API-only.
10. No agregar dependencias externas sin necesidad y aprobación.

## Orden de implementación

### Etapa 0 — Fundamentos
Leer [`docs/viaticos/00-fundamentos.md`](docs/viaticos/00-fundamentos.md).

### Etapa 1 — Solicitudes de viáticos
Leer [`docs/viaticos/01-solicitudes-viaticos.md`](docs/viaticos/01-solicitudes-viaticos.md).

> No implementar comprobaciones mientras se trabaje únicamente en esta etapa.

### Etapa 2 — Comprobaciones de viáticos
Leer [`docs/viaticos/02-comprobaciones-viaticos.md`](docs/viaticos/02-comprobaciones-viaticos.md).

### Etapa 3 — OCR y workflows
Leer [`docs/viaticos/03-ocr-workflows.md`](docs/viaticos/03-ocr-workflows.md).

> Esta etapa no forma parte del núcleo del MVP y solo debe iniciarse cuando las etapas anteriores estén estables y probadas.

## Reglas para Codex

Antes de modificar código:

1. Leer `AGENTS.md`.
2. Leer este índice.
3. Leer `docs/viaticos/00-fundamentos.md`.
4. Leer únicamente el documento de la etapa solicitada y sus prerrequisitos.
5. Inspeccionar versiones realmente instaladas y código existente.
6. Usar Laravel Boost y skills aplicables.
7. Ejecutar los tests actuales.

Durante la implementación:

- trabajar en cambios pequeños;
- mantener tests verdes;
- no adelantar funcionalidades de la etapa siguiente;
- usar migraciones Laravel como fuente de verdad;
- usar Form Requests, Policies, API Resources y Actions/Services;
- ejecutar Pint cuando se modifique PHP;
- agregar pruebas Pest por cada regla crítica.

## Criterio global de terminado

El MVP queda demostrado cuando un colaborador puede crear y firmar una solicitud, Finanzas autorizarla y firmarla, el colaborador crear una o varias comprobaciones sobre esa solicitud, registrar gastos/evidencias, recibir cálculo de límites y excedentes, corregir rechazos parciales y finalmente obtener una comprobación aprobada y firmada por Finanzas con trazabilidad histórica.

## Fuera de alcance inmediato

- frontend completo;
- multiempresa/multisucursal;
- reembolso sin solicitud previa;
- jefe inmediato como autorizador;
- perfiles separados de Contabilidad/Contraloría;
- SAP/ERP;
- validación SAT en línea;
- OCR productivo;
- n8n productivo;
- WhatsApp;
- e.firma/X.509;
- XLSX;
- PDF firmado criptográficamente.
