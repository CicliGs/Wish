<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\WishController;
use App\Http\Controllers\WishListController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CacheController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');

Route::middleware(['auth'])->group(function () {
    Route::resource('wish-lists', WishListController::class);
    Route::prefix('wish-lists/{wishList}')->group(function () {
        Route::get('wishes', [WishController::class, 'index'])->name('wishes.index');
        Route::get('wishes/create', [WishController::class, 'create'])->name('wishes.create');
        Route::post('wishes', [WishController::class, 'store'])->name('wishes.store');
        Route::get('wishes/{wish}/edit', [WishController::class, 'edit'])->name('wishes.edit');
        Route::put('wishes/{wish}', [WishController::class, 'update'])->name('wishes.update');
        Route::delete('wishes/{wish}', [WishController::class, 'destroy'])->name('wishes.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'showCurrent'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/avatar', [ProfileController::class, 'editAvatar'])->name('profile.avatar.edit');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::get('/profile/edit-name', [ProfileController::class, 'editName'])->name('profile.edit_name');
    Route::get('/profile/edit-name', [ProfileController::class, 'editName'])->name('profile.edit_name');
    Route::post('/friends/accept/{requestId}', [ProfileController::class, 'acceptFriendRequest'])->name('friends.accept');
    Route::post('/friends/decline/{requestId}', [ProfileController::class, 'declineFriendRequest'])->name('friends.decline');
    Route::get('/friends', [FriendsController::class, 'index'])->name('friends.index');
    Route::get('/friends/search', [FriendsController::class, 'search'])->name('friends.search');
    Route::post('/friends/remove/{user}', [ProfileController::class, 'removeFriend'])->name('friends.remove');
    Route::post('/friends/request/{user}', [ProfileController::class, 'sendFriendRequest'])->name('friends.request');

});

Route::middleware('auth')->prefix('wish-lists/{wishList}')->group(function () {
    Route::post('wishes/{wish}/unreserve', [WishController::class, 'unreserve'])->name('wishes.unreserve');
    Route::post('wishes/{wish}/reserve', [WishController::class, 'reserve'])->name('wishes.reserve');
});

Route::middleware('auth')->prefix('ajax')->group(function () {
    Route::post('wishes/{wish}/unreserve', [WishController::class, 'unreserveAjax'])->name('wishes.unreserve.ajax');
    Route::post('wishes/{wish}/reserve', [WishController::class, 'reserveAjax'])->name('wishes.reserve.ajax');
});

Route::middleware('auth')->group(function () {
    Route::get('/user/{user}/wishes', [WishController::class, 'showUser'])->name('wishes.user');
    Route::get('/user/{user}/wish-list/{wishList}', [WishController::class, 'showUserWishList'])->name('wishes.user.list');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.user');
});

// Notification Routes
Route::middleware('auth')->prefix('notifications')->group(function () {
    Route::get('/unread', [NotificationController::class, 'getUnreadNotifications'])->name('notifications.unread');
    Route::post('/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});

Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/public/wish-list/{uuid}', [WishListController::class, 'public'])->name('wish-lists.public');

Route::prefix('cache')->group(function () {
    Route::get('/stats', [CacheController::class, 'stats'])->name('cache.stats');
    Route::get('/status', [CacheController::class, 'status'])->name('cache.status');
    Route::post('/clear-static', [CacheController::class, 'clearStaticContent'])->name('cache.clear-static');
    Route::post('/clear-images', [CacheController::class, 'clearImageCache'])->name('cache.clear-images');
    Route::post('/clear-assets', [CacheController::class, 'clearAssetCache'])->name('cache.clear-assets');
    Route::post('/clear-avatars', [CacheController::class, 'clearAvatarCache'])->name('cache.clear-avatars');
    Route::post('/clear-all', [CacheController::class, 'clearAll'])->name('cache.clear-all');
});

Route::get('/csrf-token', function() {
    return response()->json(['token' => csrf_token()]);
});




