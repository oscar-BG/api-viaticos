# Cómo aplicar esta documentación al repo

Debido a que el conector de GitHub devolvió `403 Resource not accessible by integration` al intentar escribir en `oscar-BG/api-viaticos`, estos archivos están preparados para aplicar localmente.

## Archivos nuevos/reemplazo

Copiar a la raíz del repo:

- `MVP_API_VIATICOS_LARAVEL.md`

Crear/copiar:

- `docs/viaticos/00-fundamentos.md`
- `docs/viaticos/01-solicitudes-viaticos.md`
- `docs/viaticos/02-comprobaciones-viaticos.md`
- `docs/viaticos/03-ocr-workflows.md`

## AGENTS.md

No reemplazar el `AGENTS.md` actual.

Copiar el contenido de `AGENTS.PROJECT.md` **antes** de la línea:

```text
<laravel-boost-guidelines>
```

De esta manera se conserva la configuración oficial generada por Laravel Boost y se agrega el contexto específico del módulo de viáticos.

## Commit sugerido

```bash
git add AGENTS.md MVP_API_VIATICOS_LARAVEL.md docs/viaticos
git commit -m "docs: define staged viaticos MVP for Codex"
git push origin main
```
