# 🤖 AGENTS.md - Guía Técnica para Agentes de IA y Desarrolladores

Bienvenido al repositorio de **AsistenciaPro** (Sistema de Control y Registro de Asistencia Digital). Este documento sirve como mapa de arquitectura, contexto técnico, convenciones de código y procedimientos operativos estándar para que cualquier agente de IA o desarrollador pueda entender, mantener o extender esta aplicación sin fricciones.

---

## 1. Resumen Ejecutivo y Propósito del Proyecto

**AsistenciaPro** es una aplicación web integral construida en **Laravel 11+ / PHP 8.3** que resuelve el proceso de recolección y control de asistencia en capacitaciones, cursos y eventos institucionales. 

### Objetivos Clave:
1. **Registro Rápido con Firma Digital**: Permitir a los participantes escanear un QR con su teléfono y firmar en un lienzo HTML5 Canvas.
2. **Autocompletado Inteligente en Base de Datos**: Identificar automáticamente al empleado o participante a través de su **Código de Empleado** o **Cédula**, cargando sus nombres, apellidos, teléfono, correo y departamento sin necesidad de reescribirlos.
3. **Proyección Pública en Vivo**: Pantalla `/event/{code}/qr` proyectable en auditorios/pantallas sin login, con contador en tiempo real y feed animado de asistentes y firmas.
4. **Panel Administrativo Robusto**: Gestión de eventos (CRUD), monitor en vivo, control de estados (abrir/cerrar registro), directorio de participantes y exportación en formatos oficiales (**PDF** con firmas incrustadas y **Excel .xlsx** estilizado).

---

## 2. Pila Tecnológica (Tech Stack)

| Capa | Tecnología / Paquete | Propósito |
|---|---|---|
| **Framework Backend** | Laravel 11 / PHP 8.3 | Arquitectura MVC, Eloquent ORM, Rutas y Validación |
| **Base de Datos** | SQLite (por defecto en `database/database.sqlite`) o MySQL | Almacenamiento relacional |
| **Frontend** | Blade Components + Tailwind CSS (CDN) + Alpine.js | Interfaz responsiva, reactividad ligera sin compilación pesada |
| **Firma Digital** | HTML5 Canvas + `signature_pad@4.1.7` (UMD) | Captura táctil y exportación en Base64 PNG |
| **Generador de QR** | `simplesoftwareio/simple-qrcode` | Generación de SVG/PNG vectoriales de alta definición |
| **Exportación PDF** | `barryvdh/laravel-dompdf` | Renderizado de hojas oficiales de asistencia con firmas |
| **Exportación Excel** | `phpoffice/phpspreadsheet` | Generación de archivos `.xlsx` con estilos corporativos |
| **Testing** | PHPUnit / Orchestra Testbench | Pruebas de integración y endpoints |

---

## 3. Estructura de Directorios y Archivos Clave

```
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AttendanceController.php           # Portal público: formulario, autocompletado, registro, confirmación, proyección QR
│   │       └── Admin/
│   │           ├── AuthController.php             # Login, sesión y logout administrativo
│   │           ├── DashboardController.php        # Métricas KPIs, eventos activos y actividad reciente
│   │           ├── EventController.php            # CRUD eventos, live feed, exports (PDF y Excel)
│   │           └── ParticipantController.php      # Directorio global e historial de asistencias
│   └── Models/
│       ├── User.php                               # Administrador
│       ├── Event.php                              # Evento/Capacitación con access_code, status, require_document, department_mode
│       ├── Participant.php                        # Catálogo de personas (employee_code, document_number, nombres, teléfono...)
│       └── Attendance.php                         # Registro de asistencia (event_id, participant_id, signature_path, timestamps)
├── database/
│   ├── migrations/                                # Esquemas relacionales con claves foráneas e índices
│   └── seeders/
│       ├── DatabaseSeeder.php                     # Admin por defecto y eventos demo
│       └── InfotepEmployeesSeeder.php             # Catálogo de empleados institucionales
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── admin.blade.php                    # Layout del panel administrativo con Sidebar y Alpine.js
│       │   └── guest.blade.php                    # Layout público responsivo
│       ├── admin/
│       │   ├── auth/login.blade.php               # Login con credenciales demo rápidas
│       │   ├── dashboard.blade.php                # Panel de control con métricas y enlaces en vivo
│       │   ├── events/                            # index, create, edit, show, live, qr
│       │   ├── participants/                      # index, show, edit
│       │   └── reports/pdf.blade.php              # Plantilla HTML/CSS para DomPDF
│       └── attendance/
│           ├── form.blade.php                     # Formulario público móvil con autocompletado y Pad de Firma
│           └── confirmation.blade.php             # Comprobante visual de registro
├── routes/
│   └── web.php                                    # Definición de rutas públicas y protegidas con middleware auth
├── tests/
│   └── Feature/AttendanceTest.php                 # Pruebas automatizadas de flujo completo
```

---

## 4. Esquema de Base de Datos y Modelos

### Tabla `events`
- `id`: bigint PK
- `access_code`: string único indexado (ej: `CAP-2026`). Utilizado en URLs y códigos QR.
- `title`: string (Título del evento o curso).
- `description`, `instructor`, `location`: strings/text.
- `event_date`, `start_time`, `end_time`: fechas y horarios.
- `status`: enum (`active`, `completed`, `cancelled`).
- `allow_registration`: boolean (Permite abrir o pausar el formulario en vivo).
- `require_document`: boolean (Indica si el campo Cédula es obligatorio u opcional).
- `department_mode`: string (`hidden`, `optional`, `required`).

### Tabla `participants`
- `id`: bigint PK
- `employee_code`: string nullable, indexado (Código de empleado).
- `document_number`: string nullable, indexado (Cédula de identidad).
- `first_name`: string (Nombres).
- `last_name`: string (Apellidos).
- `phone`: string nullable (Teléfono o Extensión).
- `email`: string nullable (Correo electrónico institucional o personal).
- `institution_department`: string nullable (Área o Departamento).

### Tabla `attendances`
- `id`: bigint PK
- `event_id`: FK `events.id` (cascadeOnDelete).
- `participant_id`: FK `participants.id` (cascadeOnDelete).
- `signature_path`: string (Ruta relativa a `storage/app/public/signatures/*.png`).
- `check_in_at`: timestamp de registro.
- `ip_address`, `user_agent`: auditoría.
- **Restricción Única**: `UNIQUE(event_id, participant_id)` (Un participante solo firma 1 vez por evento).

---

## 5. Flujos y Lógica Crítica

### A. Autocompletado Inteligente (`/api/participants/lookup`)
1. El usuario escribe en el input `employee_code` o `document_number`.
2. Una petición AJAX con *debounce* (450ms) consulta `GET /api/participants/lookup?term={valor}`.
3. El controlador limpia espacios y guiones y busca coincidencias en `employee_code` o `document_number`.
4. Si lo encuentra, devuelve JSON con los datos capitalizados para autocompletar el formulario.
5. Si no lo encuentra, los campos permanecen editables para que el participante complete sus datos, los cuales se guardarán en el catálogo al enviar.

### B. Captura y Almacenamiento de Firmas
1. El canvas utiliza `signature_pad.umd.js`.
2. Al enviar el formulario, se valida que el canvas no esté vacío (`!signaturePad.isEmpty()`).
3. Se extrae la firma como Base64 PNG (`data:image/png;base64,...`).
4. `AttendanceController` decodifica la imagen y la guarda en `storage/app/public/signatures/{event_id}_{participant_id}_{time}_{random}.png`.
5. Se accede públicamente mediante el enlace simbólico `php artisan storage:link` (`/storage/signatures/...`).

### C. Proyección Pública en Vivo (`/event/{code}/qr` y `/event/{code}/live-feed`)
1. No requiere autenticación (puede ser abierta por cualquier operador en un televisor o proyector).
2. Alpine.js ejecuta polling cada 3 segundos al endpoint `/event/{code}/live-feed`.
3. Actualiza el contador gigante de asistentes y agrega dinámicamente las tarjetas de los nuevos registrados junto a la miniatura de su firma digital.

### D. Roles y Control de Acceso (`superadmin` vs `event_admin`)
1. **Administrador General (`superadmin`)**: Tiene acceso completo a la creación, edición y eliminación de eventos, participantes, y **gestión exclusiva de usuarios del sistema (`/admin/users`)**.
2. **Administrador de Eventos (`event_admin`)**: Puede crear y gestionar eventos, monitorear en vivo, ver y descargar listas de asistencia y participantes. No tiene acceso al módulo de administración de usuarios.
3. **Módulo de Mi Perfil (`/admin/profile`)**: Cualquier usuario autenticado puede modificar su nombre, correo y cambiar su contraseña de acceso.

---

## 6. Comandos Operativos para Agentes

### Iniciar el servidor local:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Ejecutar migraciones y seeders:
```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=InfotepEmployeesSeeder
```

### Ejecutar suite de pruebas:
```bash
php artisan test
```

### Crear enlace de storage si no existe:
```bash
php artisan storage:link
```

---

## 7. Credenciales Administrativas por Defecto

- **URL de Acceso**: `/login`
- **Email**: `admin@asistencia.com`
- **Password**: `password123`
- **Rol Inicial**: `superadmin` (Administrador General)

---

## 8. Guía para Futuras Extensiones

- **Integración con Hardware**: Para lectores de código de barras / carnet físico, el input de código de empleado ya cuenta con foco automático y procesa eventos de `change` y `blur`.
- **Envío de Comprobante por Correo**: Se puede despachar un Job en cola (`SendAttendanceReceiptJob`) tras `Attendance::create()` utilizando `Mail::to($participant->email)`.
