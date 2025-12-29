#!/bin/bash

# Script de desarrollo para Cloud Storage en Zorin OS
# Este script instala las dependencias necesarias y configura el entorno de desarrollo

set -e

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo "=========================================="
echo "  Cloud Storage - Setup de Desarrollo"
echo "=========================================="
echo ""

# Verificar si estamos en el directorio del proyecto
if [ ! -f "composer.json" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde el directorio raíz del proyecto${NC}"
    exit 1
fi

echo -e "${GREEN}[1/9]${NC} Actualizando lista de paquetes..."
sudo apt update

echo ""
echo -e "${GREEN}[2/9]${NC} Instalando PHP 8.2 y extensiones..."
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-pgsql php8.2-zip \
    php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl

echo ""
echo -e "${GREEN}[3/9]${NC} Instalando PostgreSQL..."
sudo apt install -y postgresql postgresql-contrib

echo ""
echo -e "${GREEN}[4/9]${NC} Instalando Composer..."
if ! command -v composer &> /dev/null; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
else
    echo "Composer ya está instalado"
fi

echo ""
echo -e "${GREEN}[5/9]${NC} Instalando Node.js y NPM..."
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
else
    echo "Node.js ya está instalado ($(node -v))"
fi

echo ""
echo -e "${GREEN}[6/9]${NC} Configurando base de datos PostgreSQL..."
echo "Creando usuario y base de datos..."

# Crear usuario y base de datos
sudo -u postgres psql -c "DROP DATABASE IF EXISTS cloud_storage;" 2>/dev/null || true
sudo -u postgres psql -c "DROP USER IF EXISTS cloud_storage_dev;" 2>/dev/null || true
sudo -u postgres psql -c "CREATE USER cloud_storage_dev WITH PASSWORD 'dev123456';"
sudo -u postgres psql -c "CREATE DATABASE cloud_storage OWNER cloud_storage_dev;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE cloud_storage TO cloud_storage_dev;"

echo ""
echo -e "${GREEN}[7/9]${NC} Configurando archivo .env..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    
    # Configurar valores para desarrollo
    sed -i "s|APP_ENV=.*|APP_ENV=local|" .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=true|" .env
    sed -i "s|APP_URL=.*|APP_URL=http://localhost:8000|" .env
    
    sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=pgsql|" .env
    sed -i "s|DB_HOST=.*|DB_HOST=127.0.0.1|" .env
    sed -i "s|DB_PORT=.*|DB_PORT=5432|" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=cloud_storage|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=cloud_storage_dev|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=dev123456|" .env
    
    sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|" .env
    
    echo "Archivo .env creado y configurado"
else
    echo "Archivo .env ya existe, no se modificará"
fi

echo ""
echo -e "${GREEN}[8/9]${NC} Instalando dependencias de PHP..."
composer install

echo ""
echo -e "${GREEN}[9/9]${NC} Instalando dependencias de Node.js..."
npm install

echo ""
echo -e "${YELLOW}Configuración de Laravel...${NC}"

# Generar key
echo "Generando APP_KEY..."
php artisan key:generate

# Crear directorios de storage
echo "Configurando directorios de storage..."
mkdir -p storage/app/users
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Ejecutar seeders
echo "Ejecutando seeders (creando usuarios de prueba)..."
php artisan db:seed --force

# Crear enlace simbólico
echo "Creando enlace simbólico de storage..."
php artisan storage:link

echo ""
echo "=========================================="
echo -e "${GREEN}  ¡Instalación completada! 🎉${NC}"
echo "=========================================="
echo ""
echo -e "${YELLOW}Credenciales de acceso:${NC}"
echo ""
echo "  👤 Administrador:"
echo "     Email:    admin@cloudstorage.local"
echo "     Password: password"
echo ""
echo "  👤 Usuario normal:"
echo "     Email:    user@cloudstorage.local"
echo "     Password: password"
echo ""
echo -e "${GREEN}Para iniciar el servidor de desarrollo:${NC}"
echo "  php artisan serve"
echo ""
echo "Luego abre en tu navegador:"
echo "  http://localhost:8000"
echo ""
echo -e "${YELLOW}Comandos útiles:${NC}"
echo "  php artisan user:create --admin    # Crear nuevo administrador"
echo "  php artisan storage:recalculate    # Recalcular espacio usado"
echo "  php artisan storage:cleanup        # Limpiar archivos huérfanos"
echo "  php artisan tinker                 # Consola interactiva"
echo ""
