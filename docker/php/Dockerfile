# docker/php/Dockerfile
FROM php:8.3-fpm-alpine

# Habilitar el repositorio 'community' de Alpine, donde se encuentra wkhtmltopdf
RUN echo "http://dl-cdn.alpinelinux.org/alpine/latest-stable/community" >> /etc/apk/repositories

# Instalar dependencias del sistema y extensiones de PHP
RUN apk --no-cache update && apk --no-cache add \
    icu-dev \
    libzip-dev \
    mysql-client \
    git \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    # Ahora que el repositorio community está habilitado, podemos instalarlo
    wkhtmltopdf

# Configurar e instalar las extensiones de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo_mysql \
    intl \
    zip

# Instalar Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www/symfony

# Copiar el código de la aplicación y la configuración de PHP
COPY . .
COPY ./docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Instalar dependencias de Symfony
RUN composer install --no-interaction --optimize-autoloader

# Cambiar permisos para que Symfony pueda escribir en cache y logs
RUN chown -R www-data:www-data var

CMD ["php-fpm"]