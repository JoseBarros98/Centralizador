# =============================================================================
# STAGE 1: Build assets (Node.js) - Ultra optimizada
# =============================================================================
FROM node:18-alpine AS node-builder

WORKDIR /app

# Copiar solo package files para aprovechar cache
COPY package*.json package-lock.json ./

# Instalar dependencias (necesarias para build)
RUN npm ci --no-audit --no-fund

# Copiar solo los archivos necesarios para el build
COPY resources/ resources/
COPY public/ public/
COPY vite.config.js ./
COPY tailwind.config.js* ./
COPY postcss.config.js* ./

# Build assets para producción
RUN npm run build

# =============================================================================
# STAGE 2: Composer dependencies - Ultra optimizada
# =============================================================================
FROM composer:2.6 AS composer-builder

WORKDIR /app

# Copiar composer files
COPY composer.json ./

# Instalar solo dependencias de producción (ignora composer.lock para forzar actualización)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# Copiar solo archivos PHP necesarios
COPY app/ app/
COPY bootstrap/ bootstrap/
COPY config/ config/
COPY database/ database/
COPY routes/ routes/
COPY resources/views/ resources/views/
COPY artisan artisan

# Generar autoloader optimizado
RUN composer dump-autoload --optimize --classmap-authoritative --no-scripts

# Limpiar archivos innecesarios para reducir tamaño
RUN find vendor/ -name "*.md" -delete && \
    find vendor/ -name "*.txt" -delete && \
    find vendor/ -name "*.rst" -delete && \
    find vendor/ -name "LICENSE*" -delete && \
    find vendor/ -name "CHANGELOG*" -delete && \
    find vendor/ -name "README*" -delete && \
    find vendor/ -name "docs/" -type d -exec rm -rf {} + 2>/dev/null || true && \
    find vendor/ -name "test/" -type d -exec rm -rf {} + 2>/dev/null || true && \
    find vendor/ -name "tests/" -type d -exec rm -rf {} + 2>/dev/null || true && \
    find vendor/ -name "Test/" -type d -exec rm -rf {} + 2>/dev/null || true && \
    find vendor/ -name "Tests/" -type d -exec rm -rf {} + 2>/dev/null || true && \
    find vendor/ -name "example*" -delete && \
    find vendor/ -name "demo*" -delete

# =============================================================================
# STAGE 3: Imagen final (PHP-FPM) - Ultra optimizada
# =============================================================================
FROM php:8.4-fpm-alpine AS production

# Instalar extensiones PHP en una sola capa y limpiar cache
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libpng-dev \
        jpeg-dev \
        libwebp-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
    && apk add --no-cache \
        libpng \
        libjpeg-turbo \
        libwebp \
        freetype \
        libzip \
        mysql-client \
        poppler-utils \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* \
    && rm -rf /tmp/* \
    && rm -rf /var/tmp/*

# Crear usuario no-root
RUN addgroup -g 1000 www && \
    adduser -u 1000 -G www -s /bin/sh -D www

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar solo los archivos necesarios desde composer-builder
COPY --from=composer-builder --chown=www:www /app/app ./app
COPY --from=composer-builder --chown=www:www /app/bootstrap ./bootstrap
COPY --from=composer-builder --chown=www:www /app/config ./config
COPY --from=composer-builder --chown=www:www /app/database ./database
COPY --from=composer-builder --chown=www:www /app/routes ./routes
COPY --from=composer-builder --chown=www:www /app/resources/views ./resources/views
COPY --from=composer-builder --chown=www:www /app/vendor ./vendor
COPY --from=composer-builder --chown=www:www /app/artisan ./artisan
COPY --from=composer-builder --chown=www:www /app/composer.json ./composer.json

# Copiar solo los assets compilados (no toda la carpeta public)
COPY --from=node-builder --chown=www:www /app/public/build ./public/build

# Copiar solo archivos públicos esenciales
COPY --chown=www:www public/index.php ./public/
COPY --chown=www:www public/images/ ./public/images/
COPY --chown=www:www public/favicon.ico ./public/
COPY --chown=www:www public/robots.txt ./public/

# # Copiar archivo .env para producción
#COPY --chown=www:www .env ./.env

# Crear directorios necesarios con permisos correctos
RUN mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www:www storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configuración PHP optimizada para producción
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/app.ini && \
    echo "date.timezone=America/La_Paz" >> /usr/local/etc/php/conf.d/app.ini && \
    echo "upload_max_filesize=500M" >> /usr/local/etc/php/conf.d/app.ini && \
    echo "post_max_size=500M" >> /usr/local/etc/php/conf.d/app.ini && \
    echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/app.ini && \
    echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/app.ini && \
    echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/app.ini && \
    echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/app.ini && \
    echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/app.ini && \
    echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/app.ini

# Health check ligero
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php-fpm -t || exit 1

EXPOSE 9000

USER www

CMD ["php-fpm"]
