# 📋 AsistenciaPro - Sistema de Control y Registro Digital de Asistencia

<p align="center">
  <strong>Plataforma integral en Laravel para la gestión de capacitaciones, cursos y eventos institucionales con captura de firma digital, autocompletado en tiempo real, proyección de códigos QR y exportación de reportes.</strong>
</p>

---

## 🚀 Características Principales

### ✍️ 1. Registro Público y Firma Digital en Móviles
- **Escaneo Directo**: Los participantes escanean el código QR proyectado en el salón con su cámara móvil y acceden de inmediato a la hoja de registro del evento.
- **Autocompletado Inteligente en BD**: Al escribir el **Código de Empleado** o la **Cédula**, el sistema consulta en tiempo real si el participante existe en el catálogo y autocompleta sus **Nombres**, **Apellidos**, **Teléfono**, **Correo** y **Departamento**.
- **Lienzo Táctil de Firma Digital**: Canvas HTML5 fluido con soporte para pantallas táctiles y ratón, con validación de trazo obligatorio y botón de limpieza rápida.
- **Botón de Limpieza General**: Permite restablecer todos los campos del formulario con un solo clic si se cometió un error.
- **Prevención de Duplicados**: Validación a nivel de base de datos que impide registros duplicados en un mismo evento.

### 📺 2. Pantalla de Proyección Pública en Vivo
- **Acceso Público Sin Login**: Enlace `/event/{codigo}/qr` diseñado para proyectarse en pantallas gigantes, televisores o proyectores de auditorios.
- **QR en Alta Definición**: Código QR vectorial listo para ser escaneado a distancia o impreso.
- **Streaming de Asistencia en Tiempo Real**: Contador animado y feed dinámico que muestra a las personas que van firmando con su hora de llegada y su firma digital capturada.
- **Modo Pantalla Completa**: Botón para ocultar barras de navegación y maximizar el contenido visual.

### 🛡️ 3. Panel de Control Administrativo
- **Dashboard con Métricas**: Visualización de eventos totales, eventos activos, catálogo de participantes registrados y flujo de asistencias del día.
- **Gestión de Eventos (CRUD)**: Creación y edición con código único personalizado, fechas, horarios, facilitador, ubicación y estado (activo, finalizado, cancelado).
- **Configuración Personalizada por Evento**:
  - Definir si el campo **Cédula** es *Obligatorio* u *Opcional*.
  - Configurar el campo **Departamento / Área** como *Oculto*, *Opcional* u *Obligatorio*.
  - Habilitar o pausar el registro en vivo con un interruptor.
- **Exportación de Reportes**:
  - **PDF Oficial**: Hoja de asistencia formal con encabezados institucionales, tabla numerada y la **imagen de la firma digital** incrustada de cada asistente.
  - **Excel (.xlsx)**: Hoja de cálculo estilizada con bordes, colores corporativos y ajuste automático de columnas.
- **Gestión de Administradores y Roles**:
  - **Administrador General**: Control total para crear, editar, activar/desactivar y eliminar otros administradores del sistema.
  - **Administrador de Eventos**: Acceso a la gestión de cursos, eventos y participantes, sin permisos de administración de usuarios.
- **Módulo de Configuración de Perfil**:
  - Actualización de nombre y correo.
  - Cambio seguro de contraseña con validación de clave actual.
- **Directorio Global de Participantes**: Historial cronológico por participante de todos los cursos y eventos a los que ha asistido con sus firmas.

---

## 🛠️ Requisitos del Sistema

- **PHP**: 8.2 o superior (probado en PHP 8.3)
- **Extensiones PHP**: `pdo`, `pdo_sqlite` (o `pdo_mysql`), `gd`, `fileinfo`, `zip`, `mbstring`
- **Composer**: 2.x
- **Base de Datos**: SQLite (cero configuración) o MySQL / MariaDB

---

## 📦 Instalación y Puesta en Marcha

### 1. Clonar el repositorio:
```bash
git clone https://github.com/JorgeTonos28/App_Asistencia_3.git
cd App_Asistencia_3
```

### 2. Instalar dependencias de PHP:
```bash
composer install
```

### 3. Configurar variables de entorno:
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Crear la base de datos y ejecutar migraciones con datos iniciales:
```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=InfotepEmployeesSeeder
```

### 5. Crear el enlace simbólico de almacenamiento para firmas:
```bash
php artisan storage:link
```

### 6. Iniciar el servidor local:
```bash
php artisan serve
```

La aplicación estará lista en: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔑 Credenciales de Acceso Administrador

- **URL de Login**: `http://127.0.0.1:8000/login`
- **Usuario**: `admin@asistencia.com`
- **Contraseña**: `password123`

---

## 🌐 Resumen de Rutas y Enlaces

| Ruta | Nombre de Ruta | Acceso | Descripción |
|---|---|---|---|
| `GET /login` | `login` | Público | Pantalla de inicio de sesión administrativo |
| `GET /event/{codigo}` | `attendance.form` | Público | Formulario móvil de registro con firma digital |
| `GET /event/{codigo}/qr` | `attendance.qr` | Público | Pantalla para proyectar QR y registros en vivo |
| `GET /admin` | `admin.dashboard` | Admin | Dashboard general con métricas y eventos activos |
| `GET /admin/events` | `admin.events.index` | Admin | Listado, filtros y gestión de eventos |
| `GET /admin/events/{id}` | `admin.events.show` | Admin | Detalle del evento y listado de asistentes |
| `GET /admin/events/{id}/export-pdf` | `admin.events.export_pdf` | Admin | Descarga del reporte oficial en PDF con firmas |
| `GET /admin/events/{id}/export-excel` | `admin.events.export_excel` | Admin | Descarga de la lista de asistencia en Excel (.xlsx) |
| `GET /admin/participants` | `admin.participants.index` | Admin | Directorio global de participantes e historial |

---

## 🧪 Pruebas Automatizadas

El proyecto cuenta con una suite de pruebas en PHPUnit para validar los flujos principales (autocompletado, registro con firma, proyección y exportaciones):

```bash
php artisan test
```

---

## 📄 Licencia

Este proyecto es de código abierto bajo la licencia [MIT](LICENSE).
