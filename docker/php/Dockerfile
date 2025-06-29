FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    git \
    unzip \
    libzip-dev \
    curl \
    && docker-php-ext-install zip pdo pdo_mysql

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
