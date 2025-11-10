FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Копируем composer файлы
COPY composer.json composer.lock* ./

# Устанавливаем зависимости
RUN if [ -f composer.lock ]; then \
        composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts; \
    else \
        composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts; \
    fi

# Копируем остальные файлы
COPY . /var/www/html

# Запускаем post-install скрипты (если нужно)
RUN composer dump-autoload --optimize --no-interaction || true

# Настраиваем права
RUN chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
