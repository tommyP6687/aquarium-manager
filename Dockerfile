FROM php:8.4-apache

# gd needs its image-library build deps (purged again after building); unzip
# is required by Composer to extract downloaded packages and is kept.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg-dev libfreetype6-dev zlib1g-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd pdo_mysql \
    && apt-get purge -y --auto-remove libpng-dev libjpeg-dev libfreetype6-dev zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# The app expects public/ as the webroot with config/, includes/, db/ as siblings
# (same layout as local `php -S -t public`), so Apache's docroot has to move down
# one level from the image's default.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Installed before the rest of the app is copied in, so editing PHP/JS files
# doesn't invalidate this layer's cache.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

COPY . .
