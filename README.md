# ProjectMSP — Portal de Reportes Ovnicom

Aplicación web interna de **Ovnicom** para gestión, análisis y distribución de reportes operacionales. Integra datos de múltiples sistemas externos (MSP, Odoo, GLPI, SharePoint) y los presenta en un portal unificado con autenticación 2FA y control de acceso por módulo.

**Producción:** `reportes.ovni.com` · **Local:** `localhost:8080` (Docker)

---

## Stack

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.3 · Laravel 13 |
| Frontend | Blade · Tailwind CSS · Alpine.js |
| Base de datos | MySQL 8.0 |
| Caché / Colas | Redis · Supervisor · Laravel Queue |
| PDF | Spatie/Browsershot (Chromium headless) |
| Excel | Maatwebsite/Excel 3.1 (PhpSpreadsheet) |
| IA | Laravel AI · Anthropic Claude |
| Contenedores | Docker (app + mysql + redis) |

---

## Instalación

```bash
# 1. Clonar y configurar
git clone <url-del-repo> && cd projectMSP
cp .env.example .env

# 2. Setup automático
composer run setup        # instala deps, genera key, migra, compila

# 3. Desarrollo
composer run dev          # php artisan serve + queue:listen + pail + npm run dev
```

---

## Módulos

### 1. Dashboard `/dashboard`
Panel principal con KPIs generales y accesos rápidos a todos los módulos. Tarjetas hacia Ventas, Clientes, Ejecutivas y Reasignación.

---

### 2. MSP Reports `/admin/reports/msp`
Ciclo de vida completo de reportes mensuales para clientes MSP.

**Ventana 1 — Importar Excel**
- Importa desde **SharePoint** (Microsoft Graph API) o subida manual.
- Procesamiento en chunks de 200 filas con upsert por `ticket_number`.
- Registro de batch con historial de importaciones. Re-importación posible dentro de 7 días.
- Filtra solo tickets tipo `Incidente` y `Solicitud` (excluye Cancelación, Instalación, Inspección).

**Ventana 2 — Clientes**
- Tabla de clientes con stats agregadas por período: total tickets, incidentes, solicitudes, tiempo promedio.
- Detalle por cliente con edición de email, número de cuenta y logo.

**Ventana 3 — PDF**
- Generación individual con Browsershot + Chromium headless.
- Descarga masiva en ZIP (hasta 30 PDFs).
- Vista previa en navegador antes de descargar.

**Ventana 4 — Correos**
- Envío individual o masivo vía **SendGrid** con PDF adjunto.
- Soporte de plantillas con banner de imagen y variables dinámicas: `[[cliente]]`, `[[periodo]]`, `[[incidentes]]`, `[[solicitudes]]`, `[[t_inc]]`, `[[t_sol]]`, `[[cuenta]]`.

**Chat IA**
- Asistente con Anthropic Claude. Consulta estadísticas en lenguaje natural.
- Puede generar PDFs y enviarlos por correo mediante comandos en JSON.

**Modelos:** `MspReport`, `MspClient`, `MspUploadBatch`, `MspPlantilla`

---

### 3. API MSP `/admin/api-msp`
Consulta en tiempo real de tickets desde la API MSP (ConnectWise/Halo).

- Selección de rango de fechas con **streaming SSE** de progreso al frontend.
- Enriquecimiento en paralelo con `Http::pool` (chunks de 25): time entries (EP2) + custom fields (EP3).
- Caché de custom fields de tickets cerrados: **24 horas** (no cambian).
- Exportación a Excel con estilos (encabezado púrpura, filas alternas, autosize).

**IDs de custom fields monitoreados:** Tipo de Cliente, Causa, Ubicación, Solución-Acción, Detalle-Reporte 2, Reporte 1, Daño, Solución, Ubicación Cierre, Imputable a, Pedido de Ventas, Tipo de ticket, Provincia, Teléfono, Cumplimiento de agenda.

**Service:** `MspService` · Auth: Basic · Protocolo: OData REST + paginación `@odata.nextLink`

---

### 4. META 2 `/admin/meta-2`
Métricas de calidad del servicio de **Telefonía** basadas en `Datos_Meta_2.xlsx`.

El Excel fuente tiene 3 hojas:
- **Consulta1** — 1,387 tickets base (TicketId, TicketNumber, CreatedDate, CompletedDate).
- **Combinar1** — 272 tickets enriquecidos con 20 columnas (Causa, Ubicación, Solución-Acción, Provincia, Teléfono, Duración, Cumplimiento). Duración promedio: **2.19 días**, máximo: 28.8 días.
- **CustomsFields** — 1,156 tickets con campos de cierre: Daño, Solución, Ubicación Cierre, Imputable a (Cliente / Ovnicom / Proveedor Ultima Milla).

**Lógica de cumplimiento SLA por día de semana** (fórmula `=IF(OR(...))` en Columna Q del Excel):
```
Cumple si:
  - "Cumplimiento verificación" = "Cumple", O
  - Creado en jueves (4) y duración < 4 días,  O
  - Creado en viernes (5) y duración < 4 días,  O
  - Creado en sábado  (6) y duración < 3 días
Caso contrario: No Cumple
```
Columnas calculadas con fórmulas Excel:
- `=TEXT(fecha,"MM")` → mes de creación y cierre.
- `=WEEKDAY(fecha, 2)` → día de semana (1=Lunes … 7=Domingo).

Funciones disponibles: listar tickets, búsqueda/filtro por mes-año, modal de detalle, exportar Excel, exportar PDF, stream IA SSE.

---

### 5. Encuestas `/admin/surveys`
Sistema de encuestas de satisfacción de clientes vía **WhatsApp**.

- Tipos de encuesta con campos dinámicos configurables.
- Cada tipo genera un token único para integración con bot de WhatsApp.
- Recepción de respuestas por webhook público: `POST /api/surveys/{token}` (throttle 30/min).
- Vista de respuestas por tipo con exportación a Excel.

**Modelos:** `SurveyType`, `Survey`

---

### 6. Sales `/admin/sales`
Dashboard comercial integrado con **Odoo** (JSON-RPC 2.0).

| Sub-módulo | Descripción |
|-----------|-------------|
| **Dashboard** | KPIs: leads, oportunidades, cotizaciones, ganadas, clientes en riesgo, pipeline total |
| **Pipeline** | Cotizaciones activas (`draft`/`sent`) filtradas por ejecutiva y estado |
| **Clientes** | Clasificación de riesgo: Al día (≤30d) / Atención (≤60d) / En riesgo (>60d sin factura) |
| **Ejecutivas** | Métricas individuales (leads, ganadas, pipeline, sin contacto). 4 llamadas bulk para todos |
| **Reasignación** | Clientes inactivos >60 días para reasignar ejecutiva |
| **Comisiones** | Cálculo de comisiones por ejecutiva |

**Service:** `OdooService` · Auth: UID de sesión (JSON-RPC `common.login`) · Caché Redis: 5–60 min por query · Retry automático con sesión nueva si UID expira.

---

### 7. GLPI `/admin/glpi`
Inventario de activos IT desde la API REST de **GLPI**.

- **Resumen** — conteo por tipo de activo: NetworkEquipment, Computer, Printer, Phone, Monitor, Peripheral.
  - Para NetworkEquipment: una sola llamada con `expand_dropdowns=true` (range 0-4999), agrupado en PHP por tipo con conteo de unidades en depósito. Antes hacía 2N llamadas (una por tipo).
- **Vista detallada** — listado por tipo con agrupación tipo → modelo → {total, depósito, items[]}. Filtro por nombre, orden por total/depósito/alfabético.
- **CRUD** — crear, editar, ver activo individual con selección de entidad.
- Sesión con caché de token 50 minutos, renovación automática en error 401.

**Service:** `GlpiService` · Auth: App-Token + Session-Token

---

### 8. Sincronizar `/admin/sincronizar`
Herramienta para emparejar clientes entre **Odoo** y **MSP**, sincronizando el campo `ReferenceId` en la API MSP.

**Coincidencias** — vista de matches automáticos:
- Exacto: `account_no (Odoo) = ReferenceId (MSP)`.
- Fuzzy: similitud ≥ 75% por nombre normalizado (sin acentos, sin sufijos numéricos).
- Ejecutar: sincronización en lote de los fuzzy aprobados vía `PATCH /customers/{id}`.

**Sin coincidencia** — pareo manual multi-color 1:N:
- Selecciona 1 cliente Odoo → se marca con un color (azul, rojo, verde, morado…) y queda activo.
- Selecciona N clientes MSP → todos toman el mismo color, vinculados a ese Odoo.
- Cada cliente MSP tiene su propio campo de cuenta editable (por defecto hereda el número Odoo).
- Soporta múltiples grupos en paralelo (colores distintos por grupo).
- Botón "Enlazar todos" → una sola petición `POST /sincronizar/enlazar` con array de pares.

**Caché:** Odoo partners 5 min (`odoo:sync:partners`) · MSP customers 5 min (`msp:customers:sync`).

---

### 9. Usuarios y Roles `/admin/users` · `/admin/roles`
Control de acceso basado en roles con permisos por módulo (RBAC).

- CRUD de usuarios con asignación de rol y cambio de contraseña.
- Roles con array JSON de módulos habilitados (`modulos: ['msp_reports', 'glpi', ...]`).
- **2FA obligatorio** — TOTP configurado en primer login (QR code), verificado en cada sesión.
- Middleware `CheckModuleAccess` protege cada grupo de rutas por slug de módulo.

**Módulos disponibles:** `msp_reports`, `api_msp`, `meta2`, `encuestas`, `usuarios`, `glpi`, `sales`.

**Modelos:** `User`, `Role`

---

### 10. API Customers `/admin/api-customers`
Listado de customers desde la API MSP. Reutiliza credenciales del módulo API MSP. Exporta a Excel.

---

### 11. Client Merge `/admin/client-merge`
Fusiona clientes MSP con clientes Odoo por similitud de nombre (fuzzy matching).

- Sube dos Excel: clientes MSP + clientes Odoo.
- Umbral de similitud configurable (50–100%).
- Resultado descargable: `CustomerID_MSP`, `CustomerName_MSP`, `NumeroCuenta`, `RUC`, Score, Matches.

---

## Integraciones externas

| Sistema | Protocolo | Auth | Caché |
|---------|-----------|------|-------|
| **MSP API** (ConnectWise/Halo) | OData REST | Basic Auth | 24h custom fields · 5min customers |
| **Odoo** | JSON-RPC 2.0 | UID de sesión | 5–60 min por query type |
| **GLPI** | REST | App-Token + Session-Token | 50min sesión |
| **SharePoint** | Microsoft Graph v1.0 | OAuth2 client credentials | — |
| **SendGrid** | REST | API Key | — |
| **Anthropic Claude** | REST | API Key | — |

---

## Exports Excel

| Módulo | Archivo | Contenido |
|--------|---------|-----------|
| API MSP | `tickets-msp-{fecha}.xlsx` | Todos los campos del ticket + custom fields (headers dinámicos) |
| META 2 | `meta2-{fecha}.xlsx` | Tickets de telefonía con filtros aplicados |
| Encuestas | `encuestas-{slug}-{fecha}.xlsx` | Respuestas con campos dinámicos por tipo |
| Clientes MSP | `customers-msp-{fecha}.xlsx` | CustomerName + CustomerId |
| Client Merge | `clientes-merge-{fecha}.xlsx` | Resultado del merge Odoo–MSP con score |

---

## Estructura de servicios

```
app/
├── Http/Controllers/Admin/
│   ├── MspReportController.php       ← reportes MSP: import, PDF, email, chat
│   ├── ApiMspController.php          ← tickets en tiempo real + SSE
│   ├── Meta2Controller.php           ← métricas telefonía
│   ├── GlpiController.php            ← inventario activos GLPI
│   ├── SincronizarController.php     ← sync Odoo ↔ MSP
│   ├── UserController.php            ← gestión usuarios
│   ├── RoleController.php            ← gestión roles + módulos
│   ├── ApiCustomersController.php    ← listado customers MSP
│   ├── ClientMergeController.php     ← merge Odoo–MSP por fuzzy
│   ├── MspPlantillaController.php    ← plantillas de correo
│   ├── SurveyController.php          ← encuestas (respuestas)
│   ├── SurveyTypeController.php      ← tipos de encuesta
│   └── Sales/
│       ├── SalesDashboardController.php
│       ├── SalesClientsController.php
│       ├── SalesExecutivesController.php
│       ├── SalesOverviewController.php
│       ├── SalesPipelineController.php
│       └── SalesReassignController.php
├── Services/
│   ├── MspService.php                ← MSP API: tickets, customers, PATCH referenceId
│   ├── GlpiService.php               ← GLPI API: activos, sesión, CRUD
│   ├── SharePointService.php         ← Microsoft Graph: listar/descargar/subir archivos
│   └── Sales/
│       └── OdooService.php           ← Odoo JSON-RPC: KPIs, pipeline, clientes, ejecutivas
├── Models/
│   ├── MspReport.php
│   ├── MspClient.php
│   ├── MspUploadBatch.php
│   ├── MspPlantilla.php
│   ├── User.php
│   ├── Role.php
│   ├── Survey.php
│   └── SurveyType.php
├── Exports/
│   ├── ApiMspExport.php              ← headers dinámicos, estilos
│   └── SurveyExport.php             ← campos dinámicos por tipo
└── Imports/
    └── MspReportsImport.php          ← chunks 200, upsert, filtra tipos ticket
```

---

## Variables de entorno requeridas

```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=projectmsp

# MSP API
MSP_USERNAME=
MSP_PASSWORD=
MSP_BASE_URL=

# Odoo
ODOO_URL=
ODOO_DB=
ODOO_USERNAME=
ODOO_API_KEY=

# GLPI
GLPI_BASE_URL=
GLPI_APP_TOKEN=
GLPI_USER_TOKEN=

# Azure / SharePoint
AZURE_TENANT_ID=
AZURE_CLIENT_ID=
AZURE_CLIENT_SECRET=
SHAREPOINT_SITE_URL=
SHAREPOINT_FOLDER=
SHAREPOINT_FILE=

# SendGrid
SENDGRID_API_KEY=
SENDGRID_FROM=

# Anthropic (IA)
ANTHROPIC_API_KEY=

# Browsershot (generación de PDFs)
BROWSERSHOT_CHROME_PATH=/usr/bin/chromium
BROWSERSHOT_NODE_PATH=/usr/bin/node
BROWSERSHOT_NPM_PATH=/usr/bin/npm
```

---

## Comandos útiles

```bash
# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Limpiar caché
php artisan view:clear && php artisan cache:clear
php artisan config:clear && php artisan route:clear

# Ver rutas del admin
php artisan route:list --path=admin

# Compilar assets (producción)
npm run build

# Levantar con Docker
docker compose up -d
docker compose exec app php artisan migrate
```

---

## Notas de producción

- Ejecutar `php artisan storage:link` para acceso público a imágenes y PDFs.
- Logo de PDFs en `storage/app/public/logos/ovnicom.png`.
- Los PDFs generados se guardan temporalmente en `storage/app/public/msp_pdfs/`.
- La configuración de módulos disponibles está en `config/modules.php` — agregar aquí para que aparezca automáticamente en gestión de roles.
- Los tipos de activos GLPI están en `config/glpi.php`.
