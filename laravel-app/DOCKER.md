# 🐳 Docker Quick Start

## Быстрый запуск

```bash
# 1. Переход в папку с Docker файлами
cd docker

# 2. Копирование .env файла (если еще не скопирован)
cp ../.env .

# 3. Запуск контейнеров
docker-compose up -d --build

# 4. Установка зависимостей
docker-compose exec app composer install

# 5. Настройка приложения
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate

# 6. Доступ к приложению
# http://localhost:8080
```

## Основные команды

```bash
# Запуск
docker-compose up -d

# Остановка
docker-compose down

# Логи
docker-compose logs -f

# Вход в контейнер
docker-compose exec app bash
```

## Подробная документация

См. [docker/README.md](docker/README.md) для полной документации по Docker. 