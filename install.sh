#!/bin/bash

# Script de instalación para Cloud Storage Autoalojado
# Ubuntu Server 24.04

set -e

echo "=================================="
echo "Cloud Storage - Instalación"
echo "=================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Verificar si se ejecuta como root
if [ "$EUID" -ne 0 ]; then 
    print_error "Este script debe ejecutarse como root (usa sudo)"
    exit 1
fi

# Obtener el usuario real (no root)
REAL_USER=${SUDO_USER:-$USER}
APP_DIR="/home/$REAL_USER/cloud-storage"

print_message "Instalando Cloud Storage para el usuario: $REAL_USER"
print_message "Directorio de instalación: $APP_DIR"
echo ""

# 1. Actualizar sistema
print_message "Actualizando sistema..."
apt update && apt upgrade -y

# 2. Instalar dependencias
print_message "Instalando dependencias del sistema..."
apt install -y software-properties-common ca-certificates lsb-release apt-transport-https git curl unzip

# 3. Instalar PHP 8.2
print_message "Instalando PHP 8.2..."
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-pgsql php8.2-zip \
    php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl

# 4. Instalar PostgreSQL
print_message "Instalando PostgreSQL..."
apt install -y postgresql postgresql-contrib

# 5. Instalar Nginx
print_message "Instalando Nginx..."
apt install -y nginx

# 6. Instalar Composer
print_message "Instalando Composer..."
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# 7. Instalar Node.js y NPM
print_message "Instalando Node.js..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# 8. Configurar PostgreSQL
print_message "Configurando PostgreSQL..."
su - postgres -c "psql -c \"CREATE DATABASE cloud_storage;\""
su - postgres -c "psql -c \"CREATE USER cloud_storage_user WITH PASSWORD 'change_this_password';\""
su - postgres -c "psql -c \"GRANT ALL PRIVILEGES ON DATABASE cloud_storage TO cloud_storage_user;\""
su - postgres -c "psql -c \"ALTER DATABASE cloud_storage OWNER TO cloud_storage_user;\""

# 9. Optimizar PostgreSQL para 3GB RAM
print_message "Optimizando PostgreSQL..."
PG_CONF="/etc/postgresql/$(ls /etc/postgresql | head -n1)/main/postgresql.conf"
cp $PG_CONF $PG_CONF.backup

cat >> $PG_CONF << EOF

# Optimizaciones para 3GB RAM
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
EOF

systemctl restart postgresql

# 10. Configurar PHP-FPM
print_message "Configurando PHP-FPM..."
PHP_FPM_CONF="/etc/php/8.2/fpm/pool.d/www.conf"
cp $PHP_FPM_CONF $PHP_FPM_CONF.backup

sed -i 's/pm = dynamic/pm = dynamic/' $PHP_FPM_CONF
sed -i 's/pm.max_children = .*/pm.max_children = 15/' $PHP_FPM_CONF
sed -i 's/pm.start_servers = .*/pm.start_servers = 4/' $PHP_FPM_CONF
sed -i 's/pm.min_spare_servers = .*/pm.min_spare_servers = 2/' $PHP_FPM_CONF
sed -i 's/pm.max_spare_servers = .*/pm.max_spare_servers = 6/' $PHP_FPM_CONF

PHP_INI="/etc/php/8.2/fpm/php.ini"
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' $PHP_INI
sed -i 's/post_max_size = .*/post_max_size = 100M/' $PHP_INI
sed -i 's/memory_limit = .*/memory_limit = 256M/' $PHP_INI

systemctl restart php8.2-fpm

# 11. Configurar proyecto
print_message "Configurando proyecto Laravel..."
PROJECT_DIR="/var/www/cloud-storage"

# Copiar archivos del proyecto
cp -r $(pwd) $PROJECT_DIR
cd $PROJECT_DIR

# Cambiar propietario
chown -R www-data:www-data $PROJECT_DIR

# Instalar dependencias de Composer
print_message "Instalando dependencias de Composer..."
su - $REAL_USER -c "cd $PROJECT_DIR && composer install --no-dev --optimize-autoloader"

# Instalar dependencias de NPM
print_message "Instalando dependencias de NPM..."
su - $REAL_USER -c "cd $PROJECT_DIR && npm install"

# Compilar assets
print_message "Compilando assets..."
su - $REAL_USER -c "cd $PROJECT_DIR && npm run build"

# Configurar .env
print_message "Configurando archivo .env..."
if [ ! -f "$PROJECT_DIR/.env" ]; then
    cp $PROJECT_DIR/.env.example $PROJECT_DIR/.env
    
    # Generar APP_KEY
    php artisan key:generate --force
    
    # Actualizar configuración de base de datos
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=cloud_storage/" $PROJECT_DIR/.env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=cloud_storage_user/" $PROJECT_DIR/.env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=change_this_password/" $PROJECT_DIR/.env
fi

# Configurar permisos
print_message "Configurando permisos..."
chown -R www-data:www-data $PROJECT_DIR
chmod -R 775 $PROJECT_DIR/storage $PROJECT_DIR/bootstrap/cache

# Ejecutar migraciones
print_message "Ejecutando migraciones..."
php artisan migrate --force

# Ejecutar seeders
print_message "Ejecutando seeders..."
php artisan db:seed --force

# Crear enlace simbólico de storage
php artisan storage:link

# 12. Configurar Nginx
print_message "Configurando Nginx..."
NGINX_CONF="/etc/nginx/sites-available/cloud-storage"

cat > $NGINX_CONF << 'EOF'
server {
    listen 80;
    server_name _;
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
EOF

# Habilitar sitio
ln -sf $NGINX_CONF /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Verificar configuración de Nginx
nginx -t

# Reiniciar Nginx
systemctl restart nginx

# 13. Configurar Cron
print_message "Configurando cron para tareas programadas..."
CRON_CMD="* * * * * cd $PROJECT_DIR && php artisan schedule:run >> /dev/null 2>&1"
(crontab -u www-data -l 2>/dev/null; echo "$CRON_CMD") | crontab -u www-data -

# 14. Configurar Queue Worker (opcional)
print_message "Configurando Queue Worker..."
SYSTEMD_SERVICE="/etc/systemd/system/cloud-storage-worker.service"

cat > $SYSTEMD_SERVICE << EOF
[Unit]
Description=Cloud Storage Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php $PROJECT_DIR/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable cloud-storage-worker
systemctl start cloud-storage-worker

# 15. Información final
echo ""
echo "=================================="
print_message "¡Instalación completada!"
echo "=================================="
echo ""
print_message "Accede a tu Cloud Storage en: http://$(hostname -I | awk '{print $1}')"
echo ""
print_warning "IMPORTANTE: Credenciales por defecto"
echo "  Admin:"
echo "    Email: admin@cloudstorage.local"
echo "    Password: password"
echo ""
echo "  Usuario de prueba:"
echo "    Email: user@cloudstorage.local"
echo "    Password: password"
echo ""
print_warning "SEGURIDAD:"
echo "  1. Cambia la contraseña de PostgreSQL en .env"
echo "  2. Cambia las contraseñas de los usuarios por defecto"
echo "  3. Configura SSL/HTTPS con Let's Encrypt"
echo "  4. Configura firewall (ufw)"
echo ""
print_message "Comandos útiles:"
echo "  - Ver logs: tail -f $PROJECT_DIR/storage/logs/laravel.log"
echo "  - Reiniciar servicios: systemctl restart nginx php8.2-fpm"
echo "  - Ver queue worker: systemctl status cloud-storage-worker"
echo "  - Recalcular espacio: php artisan storage:recalculate"
echo "  - Crear usuario: php artisan user:create"
echo ""
print_message "Para configurar SSL con Let's Encrypt:"
echo "  apt install certbot python3-certbot-nginx"
echo "  certbot --nginx -d tu-dominio.com"
echo ""
