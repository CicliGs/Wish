FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && mkdir -p /var/www/html/storage \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/html/storage

WORKDIR /var/www/html

COPY --chown=www-data:www-data . /var/www/html

USER www-data

# Установка зависимостей Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
    && npm install && npm run build

EXPOSE 80
