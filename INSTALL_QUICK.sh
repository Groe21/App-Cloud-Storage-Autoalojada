#!/bin/bash
# Guía de instalación rápida para emigserver
# Ubuntu 24.04 LTS con 3GB RAM

echo "======================================"
echo "Cloud Storage - Instalación Rápida"
echo "======================================"
echo ""

# 1. Clonar repositorio
echo "[1/5] Clonando repositorio..."
cd ~
git clone https://github.com/Groe21/App-Cloud-Storage-Autoalojada.git cloud-storage
cd cloud-storage

# 2. Ejecutar instalación
echo "[2/5] Ejecutando script de instalación..."
echo "Se te pedirán algunos datos de configuración..."
sudo bash install.sh

# 3. Post-instalación
echo "[3/5] Configuración post-instalación..."

# Nota: El script install.sh ya habrá configurado todo
# Solo necesitas ajustar las siguientes variables si es necesario:

echo "
====================================
Instalación completada
====================================

Accede a tu aplicación:
URL: http://$(hostname -I | awk '{print $1}')

Credenciales por defecto:
Usuario: admin
Contraseña: password

⚠️  IMPORTANTE:
1. Cambia la contraseña del admin inmediatamente
2. Configura el firewall si no lo hizo el script:
   sudo ufw allow 'Nginx Full'
   sudo ufw allow OpenSSH
   sudo ufw enable

3. Si tienes dominio, configura DNS y SSL:
   sudo apt install certbot python3-certbot-nginx
   sudo certbot --nginx -d tu-dominio.com

====================================
Comandos útiles:
====================================

Ver logs:
sudo journalctl -u cloud-storage -f

Reiniciar servicio:
sudo systemctl restart cloud-storage

Ver estado:
sudo systemctl status cloud-storage

Backup de base de datos:
pg_dump -U emilio cloud_storage > backup_\$(date +%Y%m%d).sql

====================================
"
