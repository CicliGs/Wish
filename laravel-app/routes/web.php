<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CacheController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishController;
use App\Http\Controllers\WishListController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'))->name('home');

Route::get('language/{locale}', [LanguageController::class, 'switchLanguage'])
    ->name('language.switch');

Route::get('public/wish-list/{uuid}', [WishListController::class, 'public'])
    ->name('wish-lists.public');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    // Wish Lists & Wishes
    Route::resource('wish-lists', WishListController::class);
    
    Route::prefix('wish-lists/{wishList}')->name('wishes.')->group(function () {
        Route::get('wishes', [WishController::class, 'index'])->name('index');
        Route::get('wishes/create', [WishController::class, 'create'])->name('create');
        Route::post('wishes', [WishController::class, 'store'])->name('store');
        Route::get('wishes/{wish}/edit', [WishController::class, 'edit'])->name('edit');
        Route::put('wishes/{wish}', [WishController::class, 'update'])->name('update');
        Route::delete('wishes/{wish}', [WishController::class, 'destroy'])->name('destroy');
        
        // Wish Reservations
        Route::post('wishes/{wish}/reserve', [WishController::class, 'reserve'])->name('reserve');
        Route::post('wishes/{wish}/unreserve', [WishController::class, 'unreserve'])->name('unreserve');
    });

    // User Profiles
    Route::get('profile', [ProfileController::class, 'showCurrent'])->name('profile');
    Route::get('profile/{user}', [ProfileController::class, 'show'])->name('profile.user');
    
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('edit', [ProfileController::class, 'edit'])->name('edit');
        Route::post('update', [ProfileController::class, 'update'])->name('update');
        Route::get('avatar', [ProfileController::class, 'editAvatar'])->name('avatar.edit');
        Route::post('avatar', [ProfileController::class, 'updateAvatar'])->name('avatar.update');
        Route::get('edit-name', [ProfileController::class, 'editName'])->name('edit_name');
    });

    // Friends Management
    Route::prefix('friends')->name('friends.')->group(function () {
        Route::get('/', [FriendsController::class, 'index'])->name('index');
        Route::get('search', [FriendsController::class, 'search'])->name('search');
        Route::post('request/{user}', [ProfileController::class, 'sendFriendRequest'])->name('request');
        Route::post('accept/{requestId}', [ProfileController::class, 'acceptFriendRequest'])->name('accept');
        Route::post('decline/{requestId}', [ProfileController::class, 'declineFriendRequest'])->name('decline');
        Route::post('remove/{user}', [ProfileController::class, 'removeFriend'])->name('remove');
    });

    // User Wishes (View Other Users)
    Route::get('user/{user}/wishes', [WishController::class, 'showUser'])->name('wishes.user');
    Route::get('user/{user}/wish-list/{wishList}', [WishController::class, 'showUserWishList'])->name('wishes.user.list');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('unread', [NotificationController::class, 'getUnreadNotifications'])->name('unread');
        Route::post('mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });
});

/*
|--------------------------------------------------------------------------
| Cache Management Routes
|--------------------------------------------------------------------------
*/

Route::prefix('cache')->name('cache.')->group(function () {
    Route::get('stats', [CacheController::class, 'stats'])->name('stats');
    Route::get('status', [CacheController::class, 'status'])->name('status');
    Route::post('clear-static', [CacheController::class, 'clearStaticContent'])->name('clear-static');
    Route::post('clear-images', [CacheController::class, 'clearImageCache'])->name('clear-images');
    Route::post('clear-assets', [CacheController::class, 'clearAssetCache'])->name('clear-assets');
    Route::post('clear-avatars', [CacheController::class, 'clearAvatarCache'])->name('clear-avatars');
    Route::post('clear-all', [CacheController::class, 'clearAll'])->name('clear-all');
});

/*
|--------------------------------------------------------------------------
| Utility Routes
|--------------------------------------------------------------------------
*/

Route::get('csrf-token', fn() => response()->json(['token' => csrf_token()]))
    ->name('csrf-token');
