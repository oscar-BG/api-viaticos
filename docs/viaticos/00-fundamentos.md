# Etapa 0 — Fundamentos del MVP

## Objetivo

Preparar la base de la API antes de desarrollar solicitudes y comprobaciones.

## Stack

- Laravel 13.
- PHP 8.3+.
- MySQL/MariaDB.
- Laravel Sanctum para autenticación API.
- Eloquent ORM.
- Form Requests.
- API Resources.
- Policies.
- PHP Enums.
- Actions/Services para casos de uso.
- Events/Listeners para desacoplar efectos secundarios.
- Laravel Storage para evidencias.
- Pest para pruebas.
- Pint para formato.

No instalar paquetes de roles/permisos solo para dos perfiles.

## Arquitectura

```text
Route
  ↓
Controller
  ↓
FormRequest
  ↓
Policy
  ↓
Action / Service
  ↓
Eloquent + DB Transaction
  ↓
Domain Event
  ↓
Listener / Job
```

Los Controllers deben ser delgados y no contener reglas complejas de negocio.

## Autenticación

Endpoints mínimos:

```http
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/auth/me
```

Si Sanctum aún no está configurado, instalarlo de la forma compatible con la versión real del proyecto usando Laravel Boost `search-docs` antes de ejecutar cambios.

## Usuarios y roles

Extender `users` con los datos mínimos:

| Campo | Tipo |
|---|---|
| `role` | varchar(30) |
| `employee_code` | varchar(50), nullable |
| `area` | varchar(150), nullable |
| `puesto` | varchar(150), nullable |
| `nivel_jerarquico_id` | FK nullable |
| `is_active` | boolean |

Enum conceptual:

```php
enum UserRole: string
{
    case Colaborador = 'COLABORADOR';
    case Finanzas = 'FINANZAS';
}
```

> Seguir la regla de Laravel Boost del proyecto para nombres de keys de Enums en TitleCase.

## Niveles jerárquicos

Tabla `niveles_jerarquicos`:

- `id`
- `nombre`
- `orden`
- `is_active`
- timestamps

Seeder inicial sugerido:

- 1er Nivel
- 2do Nivel
- 3er Nivel

## Conceptos de viáticos

Tabla `conceptos_viaticos`:

| Campo | Descripción |
|---|---|
| `clave` | identificador estable y único |
| `nombre` | nombre visible |
| `unidad` | `DIA`, `NOCHE`, `EVENTO` |
| `tiene_limite` | define si requiere límite |
| `diferencia_tipo_comprobante` | define si cambia por factura/vale |
| `is_active` | catálogo activo |

Seeder:

| Clave | Unidad | Límite | Distingue comprobante |
|---|---|---:|---:|
| `HOSPEDAJE` | `NOCHE` | Sí | No |
| `DESAYUNO` | `DIA` | Sí | Sí |
| `COMIDA` | `DIA` | Sí | Sí |
| `CENA` | `DIA` | Sí | Sí |
| `TRANSPORTE` | `EVENTO` | No | No |
| `EXTRAORDINARIO` | `EVENTO` | No | No |

`TRANSPORTE` y `EXTRAORDINARIO` no deben tener límites ficticios de 0 ni montos artificialmente altos.

## Tipos de comprobante

Tabla `tipos_comprobante_viatico` con:

- `FACTURA`
- `VALE_AZUL`
- `NO_APLICA`

## Límites

Tabla `viaticos_limites`:

- `nivel_jerarquico_id`
- `concepto_id`
- `tipo_comprobante_id`
- `monto_maximo decimal(12,2)`
- `is_active`
- timestamps

Índice único recomendado:

```text
nivel_jerarquico_id + concepto_id + tipo_comprobante_id
```

Reglas:

- concepto sin límite → `limite_aplicado = null`, `excedente = 0`;
- concepto con límite y sin configuración → error de configuración;
- si distingue comprobante → resolver por nivel + concepto + tipo;
- si no distingue → usar `NO_APLICA`.

## Endpoints de catálogos

```http
GET /api/v1/catalogos/conceptos
GET /api/v1/catalogos/tipos-comprobante
GET /api/v1/catalogos/mis-limites
```

`mis-limites` debe usar el nivel del usuario autenticado. No aceptar `nivel_jerarquico_id` del cliente como fuente de verdad.

## Respuesta estándar

Éxito:

```json
{
  "data": {},
  "message": "Operación realizada correctamente"
}
```

Error de negocio:

```json
{
  "message": "La operación no es válida para el estado actual.",
  "code": "ESTADO_INVALIDO",
  "errors": {}
}
```

HTTP sugeridos: `200`, `201`, `204`, `401`, `403`, `404`, `409`, `422`.

## Seguridad transversal

- No confiar en `user_id`, `role`, estados, totales, límites ni excedentes enviados por cliente.
- Obtener usuario desde la autenticación.
- Policies para recursos sensibles.
- Usar `decimal`, nunca `float`, para dinero.
- Transacciones para cambios de estado y cálculos sensibles.
- No exponer stack traces en producción.
- No usar mass assignment para estado/auditoría proveniente del request.
- Rate limit más estricto para login y firmas.

## Pest obligatorio

Cubrir al menos:

- login correcto;
- login inválido;
- ruta protegida sin token devuelve 401;
- rol del usuario se respeta;
- catálogos se consultan autenticados;
- `mis-limites` usa el nivel del usuario;
- concepto ilimitado se resuelve correctamente;
- concepto limitado sin configuración falla explícitamente.

## Terminado de etapa

La etapa termina cuando Auth + roles + catálogos + límites están migrados, sembrados, expuestos por API y cubiertos por Pest. No desarrollar todavía solicitudes ni comprobaciones si el encargo actual solo pide esta etapa.
