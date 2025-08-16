# 🐳 Docker Quick Start

## Quick Launch

```bash
# 1. Navigate to Docker folder
cd docker

# 2. Copy .env file (if not already copied)
cp ../.env .

# 3. Start containers
docker-compose up -d --build

# 4. Install dependencies
docker-compose exec app composer install

# 5. Setup application
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate

# 6. Access application
# http://localhost:8080
```

## Basic Commands

```bash
# Start
docker-compose up -d

# Stop
docker-compose down

# Logs
docker-compose logs -f

# Enter container
docker-compose exec app bash
```

## Detailed Documentation

See [docker/README.md](docker/README.md) for complete Docker documentation. 