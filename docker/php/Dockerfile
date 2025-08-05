# docker/php/Dockerfile
FROM php:8.3-fpm-alpine

# Instalar dependencias del sistema y extensiones de PHP
RUN apk --no-cache add \
    icu-dev \
    libzip-dev \
    mysql-client

RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    zip

# Instalar Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www/symfony

COPY ./docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Copiar el código de la aplicación (opcional, pero buena práctica)
COPY . .

# Instalar dependencias de Symfony
RUN composer install --no-interaction --optimize-autoloader

# Cambiar permisos para que Symfony pueda escribir en cache y logs
RUN chown -R www-data:www-data var

CMD ["php-fpm"]