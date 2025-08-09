# 🐳 Docker Configuration

Эта папка содержит все Docker файлы для запуска приложения WishList.

## 📁 Структура

```
docker/
├── docker-compose.yml    # Конфигурация Docker Compose
├── Dockerfile            # Образ приложения Laravel
├── supervisord.conf      # Конфигурация Supervisor
├── nginx/
│   └── default.conf      # Конфигурация Nginx
├── .env                  # Переменные окружения (не в git)
└── .gitignore           # Исключения для git
```

## 🚀 Быстрый старт

### 1. Подготовка
```bash
# Скопируйте .env файл из корня проекта
cp ../.env .

# Отредактируйте .env при необходимости
nano .env
```

### 2. Запуск
```bash
# Сборка и запуск контейнеров
docker-compose up -d --build

# Проверка статуса
docker-compose ps
```

### 3. Установка зависимостей
```bash
# Установка PHP зависимостей
docker-compose exec app composer install

# Генерация ключа приложения
docker-compose exec app php artisan key:generate

# Выполнение миграций
docker-compose exec app php artisan migrate
```

## 🔧 Управление контейнерами

### Основные команды
```bash
# Запуск
docker-compose up -d

# Остановка
docker-compose down

# Перезапуск
docker-compose restart

# Просмотр логов
docker-compose logs -f

# Логи конкретного сервиса
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f db
```

### Работа с приложением
```bash
# Вход в контейнер приложения
docker-compose exec app bash

# Выполнение artisan команд
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear

# Установка npm пакетов
docker-compose exec app npm install

# Сборка assets
docker-compose exec app npm run build
```

## 🌐 Доступ к сервисам

- **Приложение**: http://localhost:8080
- **База данных**: localhost:5432
  - База: `laravel`
  - Пользователь: `laravel`
  - Пароль: `secret`

## 🗄️ База данных

### Подключение к PostgreSQL
```bash
# Через Docker
docker-compose exec db psql -U laravel -d laravel

# Или через внешний клиент
psql -h localhost -p 5432 -U laravel -d laravel
```

### Резервное копирование
```bash
# Создание бэкапа
docker-compose exec db pg_dump -U laravel laravel > backup.sql

# Восстановление
docker-compose exec -T db psql -U laravel laravel < backup.sql
```

## 🔍 Отладка

### Проверка конфигурации
```bash
# Валидация docker-compose.yml
docker-compose config

# Проверка образов
docker images

# Проверка контейнеров
docker ps
```

### Очистка
```bash
# Остановка и удаление контейнеров
docker-compose down

# Удаление образов
docker-compose down --rmi all

# Удаление volumes
docker-compose down -v

# Полная очистка
docker system prune -a
```

## 📝 Переменные окружения

Основные переменные в `.env`:

```env
# База данных
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

# Приложение
APP_PORT=8080
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:...
```

## 🛠️ Настройка

### Изменение портов
Отредактируйте `.env` файл:
```env
APP_PORT=8080  # Порт для веб-приложения
DB_PORT=5432   # Порт для базы данных
```

### Изменение версий
Отредактируйте `docker-compose.yml`:
```yaml
db:
  image: postgres:16  # Версия PostgreSQL

nginx:
  image: nginx:alpine  # Версия Nginx
```

## 🚨 Устранение неполадок

### Проблемы с правами доступа
```bash
# Исправление прав на storage
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Проблемы с базой данных
```bash
# Пересоздание базы
docker-compose down -v
docker-compose up -d db
docker-compose exec app php artisan migrate:fresh
```

### Проблемы с кэшем
```bash
# Очистка всех кэшей
docker-compose exec app php artisan optimize:clear
```

---

**Примечание**: Все команды выполняются из папки `docker/` 