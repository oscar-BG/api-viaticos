# Etapa 3 — OCR, CFDI y workflows automatizados

## Objetivo

Definir puntos de extensión posteriores al MVP sin acoplar proveedores externos al dominio central.

No iniciar esta etapa hasta que solicitudes y comprobaciones estén funcionales y probadas.

## OCR / extracción

Definir una abstracción de dominio, por ejemplo:

```php
interface ReceiptExtractionService
{
    public function extract(UploadedFile|string $file): ReceiptExtractionResult;
}
```

Implementaciones futuras posibles:

- extracción manual;
- parser CFDI XML;
- Google Vision;
- Azure Document Intelligence;
- Tesseract;
- otro proveedor.

La lógica principal no debe conocer el proveedor concreto.

## Datos normalizados de extracción

Cuando existan:

- fecha;
- razón social;
- RFC;
- folio/número factura;
- UUID CFDI;
- subtotal/importe;
- IVA;
- retención;
- total;
- texto crudo;
- confidence.

La salida OCR es una **propuesta de captura**. El colaborador debe poder revisar/corregir antes del envío.

Endpoint futuro conceptual:

```http
POST /api/v1/comprobaciones/{comprobacion}/extraer-comprobante
```

## CFDI/XML

El parser XML puede ser una implementación independiente del OCR y debe validar estructura antes de poblar campos.

La validación SAT en línea queda fuera de alcance hasta que se solicite explícitamente.

## n8n / workflows

No llamar n8n desde Controllers.

Flujo recomendado:

```text
Domain Event
   ↓
Listener
   ↓
Queued Job
   ↓
Webhook n8n
```

Eventos útiles:

- `SolicitudSubmitted`
- `SolicitudApproved`
- `SolicitudRejected`
- `ComprobacionSubmitted`
- `ComprobacionApproved`
- `ComprobacionRejectedPartially`
- `ComprobacionRejectedTotally`
- `DetalleRejected`
- `EvidenceUploaded`
- `DocumentSigned`

Payload de ejemplo:

```json
{
  "event": "solicitud.submitted",
  "occurred_at": "2026-09-10T14:00:00-06:00",
  "data": {
    "solicitud_id": 123,
    "user_id": 10,
    "status": "EN_REVISION"
  }
}
```

Firmar el webhook con HMAC. Nunca enviar passwords ni tokens de Sanctum.

## Jobs externos

Las llamadas HTTP externas deben incluir:

- timeout;
- retry controlado;
- logging;
- ejecución en Queue cuando corresponda;
- idempotencia cuando el proveedor pueda recibir reintentos.

## PDF

La generación de PDF puede reaccionar a eventos como aprobación de solicitud/comprobación. No debe bloquear la transacción que cambia el estado.

El PDF deberá poder incluir posteriormente:

- datos del documento;
- firmas internas;
- hashes;
- movimientos relevantes;
- detalle de gastos y evidencias.

## Almacenamiento externo

La aplicación debe depender de Laravel Storage, no de rutas físicas específicas. Esto permitirá cambiar de disco local a S3-compatible sin reescribir el dominio.

## Notificaciones

Agregar mediante Listeners/Jobs posteriores:

- correo;
- Teams/Slack;
- WhatsApp;
- otras integraciones.

No formar parte de las reglas transaccionales centrales: si falla una notificación, no debe revertirse una aprobación ya confirmada salvo que negocio defina lo contrario.

## Criterio de terminado de esta etapa

La etapa se considera lista cuando existe una interfaz limpia para extracción, eventos estables del dominio y adaptadores/Jobs externos desacoplados y probados. Los proveedores concretos deben ser intercambiables mediante configuración.
