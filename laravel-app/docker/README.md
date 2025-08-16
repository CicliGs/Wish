# 🐳 Docker Configuration

This folder contains all Docker files for running the WishList application.

## 📁 Structure

```
docker/
├── docker-compose.yml    # Docker Compose configuration
├── Dockerfile            # Laravel application image
├── supervisord.conf      # Supervisor configuration
├── nginx/
│   └── default.conf      # Nginx configuration
├── .env                  # Environment variables (not in git)
└── .gitignore           # Git exclusions
```

## 🚀 Quick Start

### 1. Preparation
```bash
# Copy .env file from project root
cp ../.env .

# Edit .env if necessary
nano .env
```

### 2. Launch
```bash
# Build and run containers
docker-compose up -d --build

# Check status
docker-compose ps
```

### 3. Install Dependencies
```bash
# Install PHP dependencies
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate
```

## 🔧 Container Management

### Basic Commands
```bash
# Start
docker-compose up -d

# Stop
docker-compose down

# Restart
docker-compose restart

# View logs
docker-compose logs -f

# Logs for specific service
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f db
```

### Working with Application
```bash
# Enter application container
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear

# Install npm packages
docker-compose exec app npm install

# Build assets
docker-compose exec app npm run build
```

## 🌐 Service Access

- **Application**: http://localhost:8080
- **Database**: localhost:5432
  - Database: `laravel`
  - User: `laravel`
  - Password: `secret`

## 🗄️ Database

### Connect to PostgreSQL
```bash
# Through Docker
docker-compose exec db psql -U laravel -d laravel

# Or through external client
psql -h localhost -p 5432 -U laravel -d laravel
```

### Backup
```bash
# Create backup
docker-compose exec db pg_dump -U laravel laravel > backup.sql

# Restore
docker-compose exec -T db psql -U laravel laravel < backup.sql
```

## 🔍 Debugging

### Configuration Check
```bash
# Validate docker-compose.yml
docker-compose config

# Check images
docker images

# Check containers
docker ps
```

### Cleanup
```bash
# Stop and remove containers
docker-compose down

# Remove images
docker-compose down --rmi all

# Remove volumes
docker-compose down -v

# Full cleanup
docker system prune -a
```

## 📝 Environment Variables

Main variables in `.env`:

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

# Application
APP_PORT=8080
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:...
```

## 🛠️ Configuration

### Change Ports
Edit `.env` file:
```env
APP_PORT=8080  # Port for web application
DB_PORT=5432   # Port for database
```

### Change Versions
Edit `docker-compose.yml`:
```yaml
db:
  image: postgres:16  # PostgreSQL version

nginx:
  image: nginx:alpine  # Nginx version
```

## 🚨 Troubleshooting

### Permission Issues
```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Database Issues
```bash
# Recreate database
docker-compose down -v
docker-compose up -d db
docker-compose exec app php artisan migrate:fresh
```

### Cache Issues
```bash
# Clear all caches
docker-compose exec app php artisan optimize:clear
```

---

**Note**: All commands are executed from the `docker/` folder 