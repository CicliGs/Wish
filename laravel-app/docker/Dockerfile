FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm \
    supervisor \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && mkdir -p /var/www/html/storage \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/html/storage

WORKDIR /var/www/html

# Копируем файлы из корня проекта
COPY --chown=www-data:www-data . /var/www/html

# Копируем конфигурацию supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

USER www-data

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
