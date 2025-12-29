#!/bin/bash

# Script simplificado para desarrollo con SQLite
# Más fácil de configurar y sin dependencias complejas

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo "=========================================="
echo "  Cloud Storage - Setup Rápido SQLite"
echo "=========================================="
echo ""

# Verificar directorio
if [ ! -f "composer.json" ]; then
    echo -e "${RED}Error: Ejecuta este script desde el directorio del proyecto${NC}"
    exit 1
fi

echo -e "${GREEN}[1/6]${NC} Instalando PHP y extensiones básicas..."
sudo apt update
sudo apt install -y php php-cli php-mbstring php-xml php-zip php-curl php-sqlite3 php-bcmath php-intl unzip curl

echo ""
echo -e "${GREEN}[2/6]${NC} Instalando Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

echo ""
echo -e "${GREEN}[3/6]${NC} Instalando Node.js..."
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
fi

echo ""
echo -e "${GREEN}[4/6]${NC} Configurando .env para SQLite..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    
    # Configurar para SQLite
    sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=sqlite|" .env
    sed -i "s|DB_HOST=.*|#DB_HOST=127.0.0.1|" .env
    sed -i "s|DB_PORT=.*|#DB_PORT=5432|" .env
    sed -i "s|DB_DATABASE=.*|#DB_DATABASE=cloud_storage|" .env
    sed -i "s|DB_USERNAME=.*|#DB_USERNAME=postgres|" .env
    sed -i "s|DB_PASSWORD=.*|#DB_PASSWORD=|" .env
    
    sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|" .env
    sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=file|" .env
    
    echo "✓ Archivo .env configurado para SQLite"
else
    echo "⚠ .env ya existe, configurando SQLite..."
    sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=sqlite|" .env
fi

echo ""
echo -e "${GREEN}[5/6]${NC} Instalando dependencias..."

# Crear base de datos SQLite
mkdir -p database
touch database/database.sqlite
echo "✓ Base de datos SQLite creada"

# Instalar dependencias de Composer
echo "Instalando paquetes de PHP..."
composer install --no-interaction

# Instalar dependencias de NPM
echo "Instalando paquetes de Node.js..."
npm install

echo ""
echo -e "${GREEN}[6/6]${NC} Configurando Laravel..."

# Generar key si no existe
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate
fi

# Configurar storage
mkdir -p storage/app/users
mkdir -p storage/framework/{sessions,views,cache,testing}
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache

# Migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Seeders
echo "Creando usuarios de prueba..."
php artisan db:seed --force

# Storage link
php artisan storage:link 2>/dev/null || true

echo ""
echo "=========================================="
echo -e "${GREEN}  ¡Listo para desarrollo! 🎉${NC}"
echo "=========================================="
echo ""
echo -e "${YELLOW}📦 Base de datos:${NC} SQLite (database/database.sqlite)"
echo ""
echo -e "${YELLOW}👤 Credenciales:${NC}"
echo "  Admin:   admin@cloudstorage.local / password"
echo "  Usuario: user@cloudstorage.local / password"
echo ""
echo -e "${GREEN}🚀 Para iniciar:${NC}"
echo "  php artisan serve"
echo ""
echo "Luego abre: http://localhost:8000"
echo ""
