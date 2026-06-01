# API Analytics Ovnicom — Documentación Completa

**Base URL:** `https://analytics.ovni.com/api`  
**Autenticación:** Bearer Token (Laravel Sanctum)  
**Formato de respuesta:** `application/json` (excepto descarga de PDF)

---

## Índice

1. [POST /v1/auth/token](#1-post-v1authtoken) — Login, genera token
2. [DELETE /v1/auth/token](#2-delete-v1authtoken) — Revocar token
3. [GET /v1/msp/customer](#3-get-v1mspcustomer) — Buscar cliente por RUC
4. [GET /v1/reports/msp/periodos](#4-get-v1reportsmspperiodos) — Períodos disponibles del cliente
5. [GET /v1/reports/msp/pdf](#5-get-v1reportsmspdf) — Descargar PDF del reporte mensual
6. [POST /v1/msp-clients/bulk-update](#6-post-v1msp-clientsbulk-update) — Actualización masiva de clientes

---

## 1. POST `/v1/auth/token`

Genera un Bearer token con vigencia de **30 días**. Si el usuario ya tiene un token activo con el nombre `api`, se revoca automáticamente antes de crear el nuevo.

**Autenticación:** No requerida  
**Rate limit:** 5 req/min

### Request

```
POST https://analytics.ovni.com/api/v1/auth/token
Content-Type: application/json
```

**Body:**

```json
{
  "email": "cliente@empresa.com",
  "password": "contraseña"
}
```

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `email` | string | ✅ | Email del usuario registrado en el sistema |
| `password` | string | ✅ | Contraseña del usuario |

### Respuestas

**`201 Created` — Token generado exitosamente:**

```json
{
  "token": "8|HZwRnqB66KMAU0jaMsyPzWI6lNAmaEwrbNVNqDUjf6080d12",
  "token_type": "Bearer",
  "expires_at": "2026-06-23 15:00:00"
}
```

| Campo | Tipo | Descripción |
|---|---|---|
| `token` | string | Token completo a usar en `Authorization: Bearer {token}` |
| `token_type` | string | Siempre `"Bearer"` |
| `expires_at` | string | Fecha/hora de expiración en UTC (`YYYY-MM-DD HH:MM:SS`) |

**`401 Unauthorized` — Credenciales incorrectas:**

```json
{
  "message": "Credenciales incorrectas."
}
```

**`422 Unprocessable Entity` — Parámetros faltantes o inválidos:**

```json
{
  "message": "The email field is required.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### cURL

```bash
curl -X POST "https://analytics.ovni.com/api/v1/auth/token" \
  -H "Content-Type: application/json" \
  -d '{"email":"cliente@empresa.com","password":"contraseña"}'
```

> **Nota:** Guardar `token` y `expires_at` localmente. Al abrir la app, verificar si `expires_at` ya venció y renovar llamando este mismo endpoint — el token anterior se revoca automáticamente.

---

## 2. DELETE `/v1/auth/token`

Revoca el token Bearer activo del usuario autenticado. Útil para logout.

**Autenticación:** Bearer token requerido  
**Rate limit:** 10 req/min

### Request

```
DELETE https://analytics.ovni.com/api/v1/auth/token
Authorization: Bearer {token}
```

No requiere body.

### Respuestas

**`200 OK` — Token revocado:**

```json
{
  "message": "Token revocado correctamente."
}
```

**`401 Unauthorized` — Token inválido o ya expirado:**

```json
{
  "message": "Unauthenticated."
}
```

### cURL

```bash
curl -X DELETE "https://analytics.ovni.com/api/v1/auth/token" \
  -H "Authorization: Bearer {token}"
```

---

## 3. GET `/v1/msp/customer`

Busca un cliente en la API externa MSP usando su número de **RUC**. Consulta en tiempo real contra la API MSP (sin caché).

**Autenticación:** Bearer token requerido  
**Rate limit:** 30 req/min

### Request

```
GET https://analytics.ovni.com/api/v1/msp/customer?ruc=04000183
Authorization: Bearer {token}
```

**Query params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `ruc` | string | ✅ | RUC del cliente (mínimo 3 caracteres) |

### Respuestas

**`200 OK` — Cliente(s) encontrado(s):**

```json
{
  "success": true,
  "data": [
    {
      "CustomerName": "DULCERIA MOMI, S.A.",
      "PhoneMain": "+507 223-4567",
      "EmailDomain": "momi.com.pa",
      "ReferenceId": "04000183",
      "CustomerId": "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
    }
  ]
}
```

| Campo | Tipo | Descripción |
|---|---|---|
| `success` | boolean | `true` si la consulta fue exitosa |
| `data` | array | Lista de clientes que coinciden con el RUC. Puede retornar más de uno si el RUC hace match parcial |
| `data[].CustomerName` | string | Nombre oficial del cliente en MSP |
| `data[].PhoneMain` | string | Teléfono principal |
| `data[].EmailDomain` | string | Dominio de email corporativo |
| `data[].ReferenceId` | string | RUC / número de referencia del cliente |
| `data[].CustomerId` | string | ID único del cliente en el sistema MSP (UUID) |

**`200 OK` — Sin resultados** (el array `data` llega vacío):

```json
{
  "success": true,
  "data": []
}
```

**`422 Unprocessable Entity` — RUC faltante o muy corto:**

```json
{
  "message": "The ruc field is required.",
  "errors": {
    "ruc": ["The ruc field is required."]
  }
}
```

**`500 Internal Server Error` — Error al consultar la API MSP:**

```json
{
  "success": false,
  "message": "Error MSP API [503] en /customers: ..."
}
```

### cURL

```bash
curl -X GET "https://analytics.ovni.com/api/v1/msp/customer?ruc=04000183" \
  -H "Authorization: Bearer {token}"
```

> **Nota:** La búsqueda es por `contains` — el RUC `04000` puede retornar múltiples clientes cuyos `ReferenceId` contengan ese string. Usar el RUC completo para un resultado exacto.

---

## 4. GET `/v1/reports/msp/periodos`

Retorna los **meses disponibles** para los que existe un reporte MSP de un cliente específico. Los resultados se cachean por **6 horas** por cliente.

**Autenticación:** Bearer token requerido  
**Rate limit:** 60 req/min

### Request

```
GET https://analytics.ovni.com/api/v1/reports/msp/periodos?customer=DULCERIA+MOMI%2C+S.A.
Authorization: Bearer {token}
```

**Query params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `customer` | string | ✅ | Nombre exacto del cliente (debe coincidir con `CustomerName` devuelto por `/v1/msp/customer`) |

### Respuestas

**`200 OK` — Períodos encontrados:**

```json
{
  "customer": "DULCERIA MOMI, S.A.",
  "periodos": [
    { "value": "April 2026",    "label": "Abril 2026" },
    { "value": "March 2026",    "label": "Marzo 2026" },
    { "value": "February 2026", "label": "Febrero 2026" },
    { "value": "January 2026",  "label": "Enero 2026" }
  ]
}
```

| Campo | Tipo | Descripción |
|---|---|---|
| `customer` | string | Nombre del cliente consultado |
| `periodos` | array | Lista de períodos ordenados del más reciente al más antiguo |
| `periodos[].value` | string | Valor en inglés — **usar este campo** al llamar `/v1/reports/msp/pdf` |
| `periodos[].label` | string | Etiqueta en español — mostrar al usuario en la interfaz |

**`404 Not Found` — Cliente no tiene reportes:**

```json
{
  "error": "No se encontraron períodos para ese cliente.",
  "customer": "DULCERIA MOMI, S.A."
}
```

**`422 Unprocessable Entity` — Parámetro `customer` faltante:**

```json
{
  "message": "The customer field is required.",
  "errors": {
    "customer": ["The customer field is required."]
  }
}
```

### cURL

```bash
curl -X GET "https://analytics.ovni.com/api/v1/reports/msp/periodos" \
  -H "Authorization: Bearer {token}" \
  -G \
  --data-urlencode "customer=DULCERIA MOMI, S.A."
```

> **Importante:** El campo `value` de cada período es el que debes enviar al endpoint de descarga de PDF — no uses `label`.

---

## 5. GET `/v1/reports/msp/pdf`

Descarga el reporte mensual de soporte MSP en formato **PDF** para un cliente y período. La primera generación puede tardar unos segundos (Chromium genera el PDF); las siguientes se sirven desde caché (TTL: 48h).

**Autenticación:** Bearer token requerido  
**Rate limit:** 20 req/min

### Request

```
GET https://analytics.ovni.com/api/v1/reports/msp/pdf?customer=DULCERIA+MOMI%2C+S.A.&periodo=April+2026
Authorization: Bearer {token}
```

**Query params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `customer` | string | ✅ | Nombre exacto del cliente |
| `periodo` | string | ✅ | Período en inglés — usar el campo `value` devuelto por `/v1/reports/msp/periodos` |

### Respuestas

**`200 OK` — Archivo PDF:**

```
Content-Type: application/pdf
Content-Disposition: attachment; filename="DULCERIA_MOMI_SA_April_2026.pdf"

<binary PDF content>
```

La respuesta es el archivo binario del PDF, no JSON. Guardarlo directamente en disco o mostrarlo en un visor de PDF.

**`404 Not Found` — Cliente o período no existe:**

```json
{
  "error": "No se encontraron reportes para ese cliente y período.",
  "customer": "DULCERIA MOMI, S.A.",
  "periodo": "April 2026"
}
```

**`422 Unprocessable Entity` — Parámetros faltantes:**

```json
{
  "message": "The customer field is required.",
  "errors": {
    "customer": ["The customer field is required."],
    "periodo": ["The periodo field is required."]
  }
}
```

**`500 Internal Server Error` — Error al generar el PDF:**

```json
{
  "error": "Error al generar el PDF: <detalle del error>"
}
```

### cURL

```bash
curl -X GET "https://analytics.ovni.com/api/v1/reports/msp/pdf" \
  -H "Authorization: Bearer {token}" \
  -G \
  --data-urlencode "customer=DULCERIA MOMI, S.A." \
  --data-urlencode "periodo=April 2026" \
  --output "reporte.pdf"
```

> **Nota:** Siempre usar el valor de `value` (ej. `"April 2026"`) del endpoint de períodos — nunca el `label` en español.

---

## 6. POST `/v1/msp-clients/bulk-update`

Actualiza en lote los campos `email_cliente` y/o `numero_cuenta` de clientes MSP en la base de datos interna. Solo actualiza clientes **que ya existen** — no crea registros nuevos. Acepta hasta **1000 clientes** por request.

**Autenticación:** Bearer token requerido  
**Rate limit:** 10 req/min

### Request

```
POST https://analytics.ovni.com/api/v1/msp-clients/bulk-update
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**

```json
{
  "clients": [
    {
      "customer_name": "DULCERIA MOMI, S.A.",
      "email_cliente": "contacto@momi.com.pa",
      "numero_cuenta": "CTA-001234"
    },
    {
      "customer_name": "EMPRESA XYZ, S.A.",
      "email_cliente": "admin@xyz.com"
    },
    {
      "customer_name": "CLIENTE SIN CAMPOS"
    }
  ]
}
```

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `clients` | array | ✅ | Lista de clientes a actualizar (1–1000 elementos) |
| `clients[].customer_name` | string | ✅ | Nombre exacto del cliente (usado para buscarlo en BD) |
| `clients[].email_cliente` | string (email) | ❌ | Email del cliente. Si se omite o es `null`, no se toca |
| `clients[].numero_cuenta` | string | ❌ | Número de cuenta. Si se omite o es `null`, no se toca |

> Un cliente con `customer_name` pero sin `email_cliente` ni `numero_cuenta` es ignorado (`skipped`).

### Respuestas

**`200 OK` — Procesado (con o sin errores parciales):**

```json
{
  "updated": 2,
  "skipped": 1,
  "errors": [],
  "total": 3
}
```

| Campo | Tipo | Descripción |
|---|---|---|
| `updated` | integer | Clientes que se encontraron en BD y se actualizaron correctamente |
| `skipped` | integer | Clientes ignorados: no existen en BD, o no tenían campos válidos que actualizar |
| `errors` | array | Lista de strings con errores inesperados por cliente. Normalmente vacío |
| `total` | integer | Total de elementos enviados en el request |

**Ejemplo con errores parciales:**

```json
{
  "updated": 1,
  "skipped": 1,
  "errors": ["EMPRESA XYZ, S.A.: SQLSTATE[22001]: String data, right truncated"],
  "total": 3
}
```

**`422 Unprocessable Entity` — Validación fallida:**

```json
{
  "message": "The clients field is required.",
  "errors": {
    "clients": ["The clients field is required."],
    "clients.0.email_cliente": ["The clients.0.email_cliente must be a valid email address."]
  }
}
```

### cURL

```bash
curl -X POST "https://analytics.ovni.com/api/v1/msp-clients/bulk-update" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "clients": [
      {
        "customer_name": "DULCERIA MOMI, S.A.",
        "email_cliente": "contacto@momi.com.pa",
        "numero_cuenta": "CTA-001234"
      }
    ]
  }'
```

---

## Flujo completo — App móvil

```
1. Login
   POST /v1/auth/token
   → guardar token + expires_at en storage local

2. Al abrir la app
   ¿expires_at vencido? → POST /v1/auth/token de nuevo (renueva automáticamente)

3. Pantalla principal — buscar cliente
   GET /v1/msp/customer?ruc={ruc}
   → obtener CustomerName del cliente

4. Listar meses disponibles
   GET /v1/reports/msp/periodos?customer={CustomerName}
   → mostrar lista usando campo "label" (español)

5. Usuario selecciona un mes → descargar reporte
   GET /v1/reports/msp/pdf?customer={CustomerName}&periodo={value}
   → recibir PDF binario y mostrarlo / guardarlo

6. Logout (opcional)
   DELETE /v1/auth/token
   → revocar token del servidor
```

---

## Códigos de respuesta

| Código | Significado |
|---|---|
| `200` | Éxito |
| `201` | Creado (token generado) |
| `401` | Credenciales incorrectas o token inválido/expirado |
| `404` | Recurso no encontrado |
| `422` | Parámetros faltantes o con formato inválido |
| `429` | Demasiadas solicitudes (rate limit alcanzado) |
| `500` | Error interno del servidor |

---

## Rate limits

| Endpoint | Límite |
|---|---|
| `POST /v1/auth/token` | 5 req/min |
| `DELETE /v1/auth/token` | 10 req/min |
| `GET /v1/msp/customer` | 30 req/min |
| `GET /v1/reports/msp/periodos` | 60 req/min |
| `GET /v1/reports/msp/pdf` | 20 req/min |
| `POST /v1/msp-clients/bulk-update` | 10 req/min |
