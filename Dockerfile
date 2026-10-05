# Backend POS Men Gede: Laravel + Apache dalam satu image.
# Dipakai bersama docker-compose.yml (database MariaDB terpisah).

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist \
    --no-interaction --ignore-platform-reqs
COPY . .
# Script package:discover dijalankan saat container start (butuh env lengkap)
RUN composer dump-autoload --optimize --no-dev --no-scripts

FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql zip gd bcmath opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Dokumen web = folder public Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf

COPY docker/php.ini /usr/local/etc/php/conf.d/pos.ini
COPY --from=vendor /app /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/pos-entrypoint
RUN chmod +x /usr/local/bin/pos-entrypoint \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

ENTRYPOINT ["pos-entrypoint"]
CMD ["apache2-foreground"]
