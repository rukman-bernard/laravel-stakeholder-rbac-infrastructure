# syntax=docker/dockerfile:1

# -----------------------------------------------------------------------------
# Base: PHP-FPM for Laravel
# -----------------------------------------------------------------------------
FROM php:8.2-fpm

WORKDIR /var/www/html

# -----------------------------------------------------------------------------
# System packages + PHP extensions
# - Keep this list tight and well grouped so future edits are safe.
# - libpng-dev: gd
# - libzip-dev: zip
# - libonig-dev: mbstring
# - libxml2-dev: xml-related packages (safe common dependency)
# -----------------------------------------------------------------------------
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        curl \
        zip \
        unzip \
        cron \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
    ; \
    \
    docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
    ; \
    \
    pecl install redis; \
    docker-php-ext-enable redis; \
    \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# -----------------------------------------------------------------------------
# Composer (copied from official composer image)
# -----------------------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------------------------------
# Entrypoint (your Laravel runtime bootstrap script)
# -----------------------------------------------------------------------------
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]