# Cloud Storage Autoalojada

Aplicación de almacenamiento en la nube autoalojada construida con Laravel, diseñada para uso personal con capacidad de escalamiento futuro.

## Stack Tecnológico

- **Backend**: Laravel 11.x
- **Frontend**: Blade + Bootstrap 5
- **Base de datos**: PostgreSQL
- **Servidor**: Ubuntu Server 24.04
- **Autenticación**: Laravel Auth (sessions + middleware)
- **API REST**: Incluida desde el inicio

## Características

### Gestión de Usuarios
- Registro, login y logout
- Sistema de roles (admin/user)
- Asignación de cuotas de almacenamiento
- Visualización de espacio usado/disponible

### Gestión de Archivos
- Subida y descarga de archivos
- Organización por carpetas
- Cálculo automático de espacio usado
- Almacenamiento de metadatos en BD

### Panel Administrativo
- Dashboard con métricas del sistema
- Gestión de usuarios y cuotas
- Monitoreo de recursos del servidor
- Visualización de uso de memoria y disco

### Seguridad
- Validación de tipos de archivo
- Límites de tamaño por archivo
- Control de acceso por usuario
- Protección contra accesos no autorizados

## Requisitos del Sistema

- PHP >= 8.2
- PostgreSQL >= 14
- Composer
- Node.js y NPM (para assets)
- 3GB RAM mínimo

## Instalación

### 1. Clonar repositorio

```bash
cd /var/www
git clone <repository-url> cloud-storage
cd cloud-storage
```

### 2. Instalar dependencias

```bash
composer install
npm install && npm run build
```

### 3. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con las credenciales de PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cloud_storage
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

### 4. Crear base de datos

```bash
sudo -u postgres psql
CREATE DATABASE cloud_storage;
\q
```

### 5. Ejecutar migraciones

```bash
php artisan migrate
php artisan db:seed
```

### 6. Configurar storage

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. Configurar cron para métricas

Añadir a crontab:

```bash
* * * * * cd /var/www/cloud-storage && php artisan schedule:run >> /dev/null 2>&1
```

## Configuración del Servidor

### Nginx

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/cloud-storage/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### PHP-FPM (optimizado para 3GB RAM)

Editar `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 15
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500

upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 256M
```

### PostgreSQL (optimizado para 3GB RAM)

Editar `/etc/postgresql/14/main/postgresql.conf`:

```ini
shared_buffers = 768MB
effective_cache_size = 2GB
maintenance_work_mem = 192MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1
effective_io_concurrency = 200
work_mem = 8MB
min_wal_size = 1GB
max_wal_size = 4GB
```

## Uso

### Crear usuario administrador

```bash
php artisan user:create --admin
```

### Recalcular espacio usado

```bash
php artisan storage:recalculate
```

### Limpiar archivos huérfanos

```bash
php artisan storage:cleanup
```

## Estructura del Proyecto

```
app/
├── Console/
│   └── Commands/           # Comandos Artisan
├── Http/
│   ├── Controllers/        # Controladores
│   ├── Middleware/         # Middleware personalizado
│   └── Requests/          # Form Requests
├── Models/                # Modelos Eloquent
├── Policies/              # Políticas de autorización
├── Repositories/          # Capa de repositorio
├── Services/              # Lógica de negocio
└── Jobs/                  # Jobs en cola

database/
├── migrations/            # Migraciones
└── seeders/              # Seeders

resources/
└── views/                # Vistas Blade

routes/
├── web.php               # Rutas web
└── api.php               # Rutas API
```

## API REST

### Endpoints principales

#### Autenticación
- `POST /api/login` - Login
- `POST /api/logout` - Logout

#### Archivos
- `GET /api/files` - Listar archivos
- `POST /api/files` - Subir archivo
- `GET /api/files/{id}` - Descargar archivo
- `DELETE /api/files/{id}` - Eliminar archivo

#### Carpetas
- `GET /api/folders` - Listar carpetas
- `POST /api/folders` - Crear carpeta
- `DELETE /api/folders/{id}` - Eliminar carpeta

#### Métricas (solo admin)
- `GET /api/metrics/server` - Métricas del servidor
- `GET /api/metrics/storage` - Uso de almacenamiento

## Migración a S3/MinIO

Para migrar a almacenamiento S3 compatible:

1. Instalar el driver de S3:
```bash
composer require league/flysystem-aws-s3-v3
```

2. Configurar `.env`:
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=tu_access_key
AWS_SECRET_ACCESS_KEY=tu_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=tu_bucket
AWS_ENDPOINT=http://tu-minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

3. No se requieren cambios en el código, todo está preparado para el cambio.

## Optimización de Recursos

### Caché
- Las métricas del servidor se cachean por 5 minutos
- Las consultas frecuentes usan caché de base de datos
- Redis opcional para mejor rendimiento

### Jobs en Cola
- El cálculo de espacio usado se procesa en background
- Las operaciones pesadas usan jobs
- Queue driver configurado en base de datos (sin dependencias)

### Índices de Base de Datos
- Índices en campos frecuentemente consultados
- Índices compuestos para queries complejas
- Optimización de queries N+1

## Seguridad

- Validación de tipos MIME
- Sanitización de nombres de archivo
- Políticas de acceso por usuario
- Middleware de cuota antes de subida
- CSRF protection habilitado
- Rate limiting en API

## Monitoreo

### Logs
- Logs de aplicación en `storage/logs`
- Logs de actividad de usuarios en BD
- Logs de errores para debugging

### Métricas
- Uso de CPU y RAM
- Uso de disco por usuario
- Número de archivos por usuario
- Actividad del sistema

## Troubleshooting

### Error de permisos
```bash
sudo chown -R www-data:www-data /var/www/cloud-storage
sudo chmod -R 775 storage bootstrap/cache
```

### Error de espacio
```bash
php artisan storage:recalculate
php artisan storage:cleanup
```

### Limpiar caché
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Licencia

Propietario - Uso personal

## Contacto

Para soporte o consultas, contactar al administrador del sistema.
