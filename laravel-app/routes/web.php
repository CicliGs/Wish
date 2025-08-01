<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\WishController;
use App\Http\Controllers\WishListController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// Роут для переключения языка
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
        Route::post('wishes/{wish}/unreserve', [WishController::class, 'unreserve'])->name('wishes.unreserve');
        Route::post('wishes/{wish}/reserve', [WishController::class, 'reserve'])->name('wishes.reserve');
    });

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/avatar', [ProfileController::class, 'editAvatar'])->name('profile.avatar.edit');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::get('/profile/edit-name', [ProfileController::class, 'editName'])->name('profile.edit_name');
    Route::post('/profile/update-name', [ProfileController::class, 'updateName'])->name('profile.update_name');
    Route::get('/friends/search', [ProfileController::class, 'searchFriends'])->name('friends.search');
    Route::post('/friends/remove/{userId}', [ProfileController::class, 'removeFriend'])->name('friends.remove');
    Route::post('/friends/request/{userId}', [ProfileController::class, 'sendFriendRequest'])->name('friends.request');
    Route::post('/friends/accept/{requestId}', [ProfileController::class, 'acceptFriendRequest'])->name('friends.accept');
    Route::post('/friends/decline/{requestId}', [ProfileController::class, 'declineFriendRequest'])->name('friends.decline');
    Route::get('/friends', [\App\Http\Controllers\FriendsController::class, 'index'])->name('friends.index');
});

Route::get('/user/{userId}/wishes', [WishController::class, 'showUser'])->name('wishes.user');
Route::get('/user/{userId}/wish-list/{wishListId}', [WishController::class, 'showUserWishList'])->name('wishes.user.list');

Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/public/wish-list/{publicId}', [WishListController::class, 'public'])->name('wish-lists.public');
