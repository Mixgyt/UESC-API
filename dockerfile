# Etapa 1: Build de assets con Node.js (Tailwind 4)
FROM node:20-alpine AS node-builder

WORKDIR /app

# Aumentar memoria para Node
ENV NODE_OPTIONS="--max-old-space-size=2048"

# Copiar archivos de configuración
COPY package.json package-lock.json* ./
COPY vite.config.js ./

# Instalar dependencias de Node
# Si existe package-lock.json usa npm ci, sino usa npm install
RUN if [ -f package-lock.json ]; then npm ci --prefer-offline --no-audit; else npm install --prefer-offline --no-audit; fi

# Copiar archivos de recursos y CSS
COPY resources ./resources
COPY public ./public

# Tailwind 4 usa @import en CSS, asegurarse de copiar todo
COPY app ./app

# Build de assets
RUN npm run build

# Etapa 2: Imagen principal de PHP
FROM php:8.4-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Copiar configuración personalizada de PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Configurar opcache para producción
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www

# Copiar archivos de dependencias primero (cache layer)
COPY composer.json composer.lock ./

# Instalar dependencias de PHP sin scripts
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copiar el resto del código
COPY . .

# Copiar assets compilados desde la etapa de Node
COPY --from=node-builder /app/public/build ./public/build

# Completar instalación de Composer y generar autoload optimizado
RUN composer dump-autoload --optimize --classmap-authoritative

# Crear directorios necesarios y configurar permisos
RUN mkdir -p /var/www/storage/framework/cache/data \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/logs \
    /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Configurar Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

# Configurar Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar y configurar entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
