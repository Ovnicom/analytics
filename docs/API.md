# API Analytics Ovnicom — Documentación

**Base URL:** `https://analytics.ovni.com/api`  
**Autenticación:** Bearer Token (Sanctum)  
**Formato:** JSON (excepto descarga de PDF)

---

## Autenticación

### POST `/v1/auth/token`
Genera un Bearer token con vigencia de 30 días. Al llamarlo revoca cualquier token anterior del mismo usuario.

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "email": "cliente@empresa.com",
  "password": "contraseña"
}
```

**Respuesta exitosa `201`:**
```json
{
  "token": "8|HZwRnqB66KMAU0jaMsyPzWI6lNAmaEwrbNVNqDUjf6080d12",
  "token_type": "Bearer",
  "expires_at": "2026-06-23 15:00:00"
}
```

**Respuesta error `401`:**
```json
{
  "message": "Credenciales incorrectas."
}
```

**cURL:**
```bash
curl -X POST "https://analytics.ovni.com/api/v1/auth/token" \
  -H "Content-Type: application/json" \
  -d '{"email":"cliente@empresa.com","password":"contraseña"}'
```

> **Renovación:** Llamar este endpoint cada vez que el usuario abre la app y el `expires_at` ya venció. El token anterior se revoca automáticamente.

---

## Consultar cliente por RUC

### GET `/v1/msp/customer`
Busca un cliente en el sistema MSP usando su número de RUC.

**Headers:**
```
Authorization: Bearer {token}
```

**Query params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `ruc` | string | ✅ | Número de RUC del cliente |

**Respuesta exitosa `200`:**
```json
{
  "customer": { ... }
}
```

**Respuesta error `404`:**
```json
{
  "error": "Cliente no encontrado."
}
```

**cURL:**
```bash
curl -X GET "https://analytics.ovni.com/api/v1/msp/customer?ruc=04000183" \
  -H "Authorization: Bearer {token}"
```

---

## Períodos disponibles

### GET `/v1/reports/msp/periodos`
Retorna los períodos (meses) para los que existe reporte de un cliente.

**Headers:**
```
Authorization: Bearer {token}
```

**Query params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `customer` | string | ✅ | Nombre exacto del cliente |

**Respuesta exitosa `200`:**
```json
{
  "customer": "DULCERIA MOMI, S.A.",
  "periodos": [
    { "value": "April 2026", "label": "Abril 2026" },
    { "value": "March 2026", "label": "Marzo 2026" },
    { "value": "February 2026", "label": "Febrero 2026" }
  ]
}
```

> `value` — enviar en el request de descarga de PDF.  
> `label` — mostrar al usuario en la app.

**Respuesta error `404`:**
```json
{
  "error": "No se encontraron períodos para ese cliente.",
  "customer": "DULCERIA MOMI, S.A."
}
```

**cURL:**
```bash
curl -X GET "https://analytics.ovni.com/api/v1/reports/msp/periodos" \
  -H "Authorization: Bearer {token}" \
  -G \
  --data-urlencode "customer=DULCERIA MOMI, S.A."
```

---

## Descargar PDF de reporte

### GET `/v1/reports/msp/pdf`
Descarga el reporte PDF mensual de un cliente. La primera generación tarda unos segundos; las siguientes se sirven desde caché.

**Headers:**
```
Authorization: Bearer {token}
```

**Query params:**

| Parámetro | Tipo | Requerido | Descripción |
|---|---|---|---|
| `customer` | string | ✅ | Nombre exacto del cliente |
| `periodo` | string | ✅ | Valor del período (campo `value` del endpoint de períodos) |

**Respuesta exitosa `200`:**
```
Content-Type: application/pdf
Binary PDF file
```

**Respuesta error `404`:**
```json
{
  "error": "No se encontraron reportes para ese cliente y período.",
  "customer": "DULCERIA MOMI, S.A.",
  "periodo": "April 2026"
}
```

**Respuesta error `500`:**
```json
{
  "error": "Error al generar el PDF: ..."
}
```

**cURL:**
```bash
curl -X GET "https://analytics.ovni.com/api/v1/reports/msp/pdf" \
  -H "Authorization: Bearer {token}" \
  -G \
  --data-urlencode "customer=DULCERIA MOMI, S.A." \
  --data-urlencode "periodo=April 2026" \
  --output "reporte.pdf"
```

---

## Flujo completo de la app

```
1. Login
   POST /v1/auth/token → guardar token + expires_at

2. Al abrir la app
   ¿expires_at vencido? → renovar token (mismo endpoint)

3. Pantalla principal
   GET /v1/msp/customer?ruc={ruc}                 → datos del cliente
   GET /v1/reports/msp/periodos?customer={nombre} → listar meses disponibles

4. El usuario selecciona un mes → descargar
   GET /v1/reports/msp/pdf?customer={nombre}&periodo={value} → PDF
```

---

## Códigos de respuesta

| Código | Significado |
|---|---|
| `200` | Éxito |
| `201` | Creado (token generado) |
| `401` | Credenciales incorrectas o token inválido |
| `404` | Recurso no encontrado |
| `422` | Parámetros faltantes o inválidos |
| `429` | Demasiadas solicitudes (rate limit) |
| `500` | Error interno del servidor |

---

## Rate limits

| Endpoint | Límite |
|---|---|
| `POST /v1/auth/token` | 5 req/min |
| `GET /v1/msp/customer` | 30 req/min |
| `GET /v1/reports/msp/periodos` | 60 req/min |
| `GET /v1/reports/msp/pdf` | 20 req/min |
