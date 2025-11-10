FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql bcmath \
    && docker-php-ext-enable bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Копируем composer файлы
COPY composer.json composer.lock* ./

# Проверяем установленные расширения PHP
RUN php -m

# Устанавливаем зависимости (игнорируем требования платформы для bcmath, если проблема сохраняется)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts --ignore-platform-req=ext-bcmath || \
    (php -m && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts)

# Копируем остальные файлы
COPY . /var/www/html

# Запускаем post-install скрипты (если нужно)
RUN composer dump-autoload --optimize --no-interaction || true

# Настраиваем права
RUN chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
