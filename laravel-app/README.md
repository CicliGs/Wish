# 🎁 WishList - Wish List Platform

A modern web application for creating and managing wish lists, with the ability for friends to reserve gifts.

## ✨ Key Features

- **Wish List Creation** - organize your wishes by categories
- **Friends System** - add friends and share lists
- **Gift Reservation** - friends can reserve gifts from your lists
- **Achievements** - achievement system to motivate users
- **Multilingual** - support for Russian and English languages
- **Modern Design** - beautiful animations and responsive interface
- **QR Codes** - quick access to lists through QR codes
- **Public Links** - share lists through unique UUIDs

## 🚀 Technologies

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Bootstrap 5, JavaScript, CSS3
- **Database**: PostgreSQL 16
- **Containerization**: Docker & Docker Compose
- **Server**: Nginx
- **Additional**: QRious.js for QR code generation

## 📋 Requirements

- Docker & Docker Compose
- Git

## 🛠️ Installation and Setup

### 1. Clone Repository
```bash
git clone <repository-url>
cd pet-project/laravel-app
```

### 2. Environment Setup
```bash
cp .env.example .env
# Edit .env file according to your needs
```

### 3. Run with Docker
```bash
# Navigate to Docker folder
cd docker

# Build and run containers
docker-compose up -d --build

# Install PHP dependencies
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Clear cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### 4. Access Application
The application will be available at: `http://localhost:8080`

## 🗄️ Database Structure

### Main Tables:
- **users** - system users
- **wish_lists** - wish lists (with UUID for public access)
- **wishes** - wishes in lists
- **friend_requests** - friend requests
- **reservations** - gift reservations

### Key Relationships:
- A user can have multiple wish lists
- A wish list contains multiple wishes
- Users can be friends through the request system
- Friends can reserve each other's wishes
- Each wish list has a unique UUID for public access

## 🏗️ Architecture

### Controllers
- `ProfileController` - user profile management
- `WishListController` - wish list management
- `WishController` - wish management
- `FriendsController` - friends management
- `ReservationController` - reservation management

### Services
- `ProfileService` - profile business logic
- `WishListService` - wish list business logic
- `WishService` - wish business logic
- `FriendService` - friends business logic
- `ReservationService` - reservation business logic
- `AchievementCheckers` - achievement verification

### DTO (Data Transfer Objects)
- `ProfileDTO` - profile data
- `WishListDTO` - wish list data
- `WishDTO` - wish data
- `FriendsDTO` - friends data
- `PublicWishListDTO` - public list data

## 🎯 Achievement System

The application includes an achievement system to motivate users:

- **Registration** - for registering on the site
- **First Gift** - for adding the first wish
- **First Reservation** - for the first gift reservation
- **First Friend** - for adding the first friend
- **Gift Master** - for 50+ added gifts
- **Reservation Master** - for 50+ reserved gifts
- **Social Butterfly** - for 10+ friends
- **Site Veteran** - for a month of site registration

## 🌐 Multilingual Support

The application supports two languages:
- **Russian** (default)
- **English**

Language switching is available in the navigation menu.

## 🎨 Design

- **Modern UI** with gradients and animations
- **Responsive design** for all devices
- **Glass morphism** effects
- **Smooth transitions** and hover effects
- **Custom scrollbars**
- **Modal windows** with proper z-index

## 🔧 Development

### Development Commands
```bash
# Navigate to Docker folder
cd docker

# Run in development mode
docker-compose exec app composer dev

# Run tests
docker-compose exec app composer test

# Clear cache
docker-compose exec app php artisan cache:clear

# View logs
docker-compose logs -f app
```

### Project Structure
```
app/
├── DTOs/           # Data Transfer Objects
├── Http/
│   ├── Controllers/    # Controllers
│   └── Requests/       # Request validation
├── Models/         # Eloquent models
├── Services/       # Business logic
└── Providers/      # Service providers

resources/
├── views/          # Blade templates
└── lang/           # Localization files

public/
├── css/            # Styles
├── js/             # JavaScript
└── images/         # Images

docker/
├── docker-compose.yml  # Docker Compose configuration
├── Dockerfile          # Application image
├── supervisord.conf    # Supervisor configuration
└── nginx/
    └── default.conf    # Nginx configuration
```

## 🐛 Debugging

### Useful Commands
```bash
# Navigate to Docker folder
cd docker

# Enter application container
docker-compose exec app bash

# View Laravel logs
docker-compose exec app php artisan pail

# Check migration status
docker-compose exec app php artisan migrate:status

# Clear all caches
docker-compose exec app php artisan optimize:clear
```

## 📝 License

This project is distributed under the MIT license. See the [LICENSE](LICENSE) file for more information.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

If you have questions or suggestions, create an issue in the project repository.

---

**Created with ❤️ on Laravel**
