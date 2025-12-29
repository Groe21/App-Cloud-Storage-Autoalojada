# Arquitectura del Sistema - Cloud Storage Autoalojado

## Visión General

Esta aplicación es un sistema de almacenamiento en la nube autoalojado construido con Laravel 11, diseñado para ser escalable, eficiente y fácil de mantener. Utiliza una arquitectura monolítica modular con separación clara de responsabilidades.

## Principios de Diseño

### 1. Separación de Responsabilidades
- **Controllers**: Manejan las peticiones HTTP y respuestas
- **Services**: Contienen la lógica de negocio
- **Repositories**: Abstraen el acceso a datos
- **Policies**: Gestionan la autorización
- **Jobs**: Procesan tareas en segundo plano
- **Models**: Representan entidades y relaciones

### 2. Arquitectura en Capas

```
┌─────────────────────────────────────────┐
│         Presentation Layer              │
│  (Controllers, Views, API Endpoints)    │
└─────────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────┐
│         Business Logic Layer            │
│         (Services, Policies)            │
└─────────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────┐
│          Data Access Layer              │
│        (Repositories, Models)           │
└─────────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────┐
│           Database Layer                │
│            (PostgreSQL)                 │
└─────────────────────────────────────────┘
```

## Esquema de Base de Datos

### Tablas Principales

#### users
- `id`: Identificador único
- `name`: Nombre del usuario
- `email`: Email único
- `password`: Contraseña hasheada
- `role`: Rol (admin/user)
- `is_active`: Estado del usuario
- `timestamps`: created_at, updated_at
- `soft_deletes`: deleted_at

**Relaciones:**
- hasOne: StorageQuota
- hasMany: Files, Folders, ActivityLogs

#### storage_quotas
- `id`: Identificador único
- `user_id`: FK a users
- `quota_bytes`: Cuota total en bytes
- `used_bytes`: Espacio usado en bytes
- `timestamps`: created_at, updated_at

**Relaciones:**
- belongsTo: User

#### folders
- `id`: Identificador único
- `user_id`: FK a users
- `parent_id`: FK a folders (nullable, para subcarpetas)
- `name`: Nombre de la carpeta
- `path`: Ruta completa
- `timestamps`: created_at, updated_at
- `soft_deletes`: deleted_at

**Relaciones:**
- belongsTo: User, Parent Folder
- hasMany: Child Folders, Files

**Índices:**
- `user_id`, `parent_id`, `(user_id, parent_id)`
- UNIQUE: `(user_id, parent_id, name)`

#### files
- `id`: Identificador único
- `user_id`: FK a users
- `folder_id`: FK a folders (nullable)
- `name`: Nombre del archivo en storage
- `original_name`: Nombre original del archivo
- `path`: Ruta en el storage
- `mime_type`: Tipo MIME
- `extension`: Extensión del archivo
- `size_bytes`: Tamaño en bytes
- `hash`: SHA256 hash (para deduplicación)
- `timestamps`: created_at, updated_at
- `soft_deletes`: deleted_at

**Relaciones:**
- belongsTo: User, Folder

**Índices:**
- `user_id`, `folder_id`, `mime_type`, `hash`, `(user_id, folder_id)`, `created_at`

#### server_metrics
- `id`: Identificador único
- `cpu_usage`: Uso de CPU en porcentaje
- `memory_total`, `memory_used`, `memory_free`: Memoria en bytes
- `memory_usage_percent`: Porcentaje de uso de memoria
- `disk_total`, `disk_used`, `disk_free`: Disco en bytes
- `disk_usage_percent`: Porcentaje de uso de disco
- `load_average_1`, `load_average_5`, `load_average_15`: Carga del sistema
- `recorded_at`: Timestamp de la medición
- `timestamps`: created_at, updated_at

**Índices:**
- `recorded_at`

#### activity_logs
- `id`: Identificador único
- `user_id`: FK a users (nullable)
- `action`: Tipo de acción
- `entity_type`: Tipo de entidad (File, Folder, User)
- `entity_id`: ID de la entidad
- `description`: Descripción de la acción
- `metadata`: JSON con datos adicionales
- `ip_address`: Dirección IP
- `user_agent`: User agent
- `timestamps`: created_at, updated_at

**Relaciones:**
- belongsTo: User

**Índices:**
- `user_id`, `action`, `entity_type`, `created_at`, `(entity_type, entity_id)`

## Componentes Principales

### 1. Sistema de Archivos

#### FileService
Gestiona la lógica de negocio de archivos:
- Subida de archivos con validación
- Descarga de archivos
- Eliminación de archivos
- Búsqueda y filtrado
- Cálculo de estadísticas

**Validaciones:**
- Tamaño máximo del archivo (configurable)
- Tipos de archivo permitidos (configurable)
- Verificación de cuota de almacenamiento

**Flujo de Subida:**
```
1. Validar archivo (tipo, tamaño)
2. Verificar cuota disponible
3. Generar nombre único (UUID)
4. Calcular hash SHA256
5. Almacenar archivo físico
6. Crear registro en BD
7. Actualizar cuota del usuario
8. Registrar actividad
```

#### FileRepository
Acceso a datos de archivos:
- Consultas optimizadas con índices
- Paginación
- Búsqueda por usuario y carpeta
- Cálculo de totales

### 2. Sistema de Carpetas

#### FolderService
Gestiona carpetas:
- Creación de carpetas
- Estructura jerárquica
- Eliminación en cascada
- Navegación por breadcrumbs

**Estructura Jerárquica:**
- Carpetas raíz: `parent_id = NULL`
- Subcarpetas: `parent_id = [folder_id]`
- Eliminación recursiva de subcarpetas y archivos

### 3. Sistema de Cuotas

#### StorageQuota Model
- Cálculo automático de espacio usado
- Actualización en tiempo real (hooks de modelo)
- Verificación antes de subida (middleware)

**Middleware CheckStorageQuota:**
```php
1. Interceptar petición de subida
2. Obtener tamaño del archivo
3. Verificar espacio disponible del usuario
4. Permitir o rechazar la operación
```

### 4. Sistema de Métricas

#### ServerMetricsService
Recolecta métricas del servidor:
- CPU: Uso y load average
- Memoria: Total, usado, libre
- Disco: Total, usado, libre
- Caché: 5 minutos TTL

**Fuentes de Datos:**
- `/proc/cpuinfo`: Información de CPU
- `/proc/meminfo`: Información de memoria
- `disk_total_space()`, `disk_free_space()`: Espacio en disco
- `sys_getloadavg()`: Load average

**Limpieza Automática:**
- Se mantienen solo los últimos 7 días de métricas
- Ejecución diaria a las 2:00 AM

### 5. Sistema de Jobs

#### RecalculateUserStorageJob
- Recalcula el espacio usado por un usuario
- Se ejecuta en cola para no bloquear
- Puede ser disparado manualmente por comando

#### CollectServerMetricsJob
- Recolecta métricas cada 5 minutos
- Ejecutado por el scheduler de Laravel
- Almacena en base de datos para histórico

### 6. Sistema de Permisos

#### Policies
- **FilePolicy**: Control de acceso a archivos
  - view: Usuario propietario o admin
  - delete: Usuario propietario o admin
  
- **FolderPolicy**: Control de acceso a carpetas
  - view: Usuario propietario o admin
  - update: Usuario propietario
  - delete: Usuario propietario o admin

#### AdminMiddleware
- Verifica si el usuario es administrador
- Protege rutas administrativas
- Retorna 403 si no autorizado

### 7. Sistema de Logging

#### ActivityLog Model
- Registra todas las acciones importantes
- Almacena metadata en JSON
- Captura IP y user agent
- Método estático para facilitar el logging

**Acciones Rastreadas:**
- upload, download, delete (archivos)
- create_folder, delete_folder (carpetas)
- login, logout (autenticación)
- create_user, update_user, delete_user (administración)

## API REST

### Endpoints de Archivos
```
GET    /api/files              - Listar archivos
POST   /api/files              - Subir archivo
GET    /api/files/{id}         - Ver archivo
GET    /api/files/{id}/download - Descargar archivo
DELETE /api/files/{id}         - Eliminar archivo
GET    /api/files/search       - Buscar archivos
```

### Endpoints de Carpetas
```
GET    /api/folders            - Listar carpetas
POST   /api/folders            - Crear carpeta
GET    /api/folders/tree       - Árbol completo
GET    /api/folders/{id}       - Ver carpeta
PUT    /api/folders/{id}       - Actualizar carpeta
DELETE /api/folders/{id}       - Eliminar carpeta
```

### Endpoints de Métricas (Admin)
```
GET    /api/metrics/server     - Métricas del servidor
GET    /api/metrics/storage    - Métricas de almacenamiento
GET    /api/metrics/historical - Métricas históricas
```

## Optimizaciones para Bajo Consumo de Recursos

### 1. Base de Datos
- **Índices estratégicos** en campos frecuentemente consultados
- **Paginación** en todas las listas
- **Eager loading** para evitar N+1 queries
- **Soft deletes** para no eliminar físicamente
- **Configuración PostgreSQL** optimizada para 3GB RAM

### 2. Caché
- Métricas del servidor cacheadas 5 minutos
- Uso de cache de base de datos (no requiere Redis)
- Cache de consultas frecuentes

### 3. Jobs en Cola
- Operaciones pesadas en background
- Queue driver en base de datos (sin dependencias)
- Recálculo de espacio en jobs

### 4. Almacenamiento
- Solo metadatos en base de datos
- Archivos en filesystem
- Preparado para S3/MinIO sin cambios de código
- Deduplicación por hash SHA256

### 5. Frontend
- Bootstrap 5 desde CDN (sin compilación)
- JavaScript vanilla (mínimas dependencias)
- Imágenes y assets optimizados
- Mobile-first responsive

## Seguridad

### 1. Autenticación
- Sessions de Laravel (secure, httpOnly cookies)
- Passwords hasheados con bcrypt
- CSRF protection habilitado
- Remember me tokens

### 2. Autorización
- Policies de Laravel
- Middleware de roles
- Verificación a nivel de controlador

### 3. Validación de Archivos
- Validación de tipo MIME
- Verificación de extensión
- Límite de tamaño
- Sanitización de nombres

### 4. Protección de Datos
- Solo el propietario puede acceder a sus archivos
- Admin puede ver pero no modificar archivos de usuarios
- Soft deletes para recuperación
- Logs de todas las acciones

## Escalabilidad Futura

### Preparado para:
1. **S3/MinIO**: Solo cambiar configuración de storage
2. **Redis**: Para cache y queues más rápidos
3. **Load Balancer**: Stateless design
4. **CDN**: Assets servidos desde CDN
5. **Microservicios**: Services ya separados
6. **API-First**: REST API completa disponible

### Sugerencias de Crecimiento:
1. Implementar **versionado de archivos**
2. Añadir **compartir archivos** entre usuarios
3. Implementar **cifrado de archivos**
4. Añadir **previsualización** de archivos
5. Implementar **sincronización** de escritorio
6. Añadir **colaboración** en documentos
7. Implementar **búsqueda full-text** con PostgreSQL
8. Añadir **papelera de reciclaje** con auto-eliminación

## Comandos Artisan Personalizados

```bash
# Crear usuario interactivamente
php artisan user:create [--admin]

# Recalcular espacio de almacenamiento
php artisan storage:recalculate [user_id]

# Limpiar archivos huérfanos
php artisan storage:cleanup [--dry-run]
```

## Monitoreo y Mantenimiento

### Tareas Programadas (Cron)
- **Cada 5 minutos**: Recolectar métricas del servidor
- **Diario 02:00**: Limpiar métricas antiguas
- **Diario 03:00**: Recalcular cuotas de almacenamiento

### Logs
- **Application logs**: `storage/logs/laravel.log`
- **Activity logs**: Tabla `activity_logs`
- **Web server logs**: Nginx/Apache access y error logs

### Health Checks
- Endpoint `/up` para verificar estado
- Métricas API para monitoreo externo

## Conclusión

Esta arquitectura proporciona una base sólida para un sistema de cloud storage autoalojado, con énfasis en:
- **Eficiencia**: Optimizado para recursos limitados
- **Escalabilidad**: Preparado para crecer
- **Mantenibilidad**: Código limpio y organizado
- **Seguridad**: Múltiples capas de protección
- **Flexibilidad**: Fácil de extender y modificar
