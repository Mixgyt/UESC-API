#!/bin/bash
set -e

echo "🚀 Iniciando Laravel..."

# Verificar que los assets de Vite existan
if [ ! -f "/var/www/public/build/manifest.json" ]; then
    echo "❌ ERROR: No se encontró el manifest de Vite en /var/www/public/build/manifest.json"
    echo "Los assets no fueron compilados correctamente."
    exit 1
fi

echo "✅ Manifest de Vite encontrado"

# Verificar permisos iniciales
echo "🔧 Configurando permisos..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Limpiar cachés
echo "🧹 Limpiando cachés..."
php artisan config:clear
php artisan cache:clear || echo "⚠️  No se pudo limpiar la caché"
php artisan view:clear

# Ejecutar migraciones
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force || echo "⚠️  No se pudieron ejecutar las migraciones (base de datos no lista aún)"

# Optimizar para producción
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Optimizando para producción..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Asegurar permisos finales sobre logs y caché creados durante el inicio
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

echo "✅ Aplicación lista!"

# Ejecutar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf