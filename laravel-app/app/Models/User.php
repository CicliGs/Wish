<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar', // добавлено поле для аватара
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the wish lists for the user.
     */
    public function wishLists()
    {
        return $this->hasMany(\App\Models\WishList::class);
    }

    /**
     * Get the reservations for the user.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Друзья, которых добавил пользователь
     */
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id');
    }

    /**
     * Пользователи, которые добавили этого пользователя в друзья
     */
    public function friendOf()
    {
        return $this->belongsToMany(User::class, 'friends', 'friend_id', 'user_id');
    }

    public function incomingRequests()
    {
        return $this->hasMany(FriendRequest::class, 'friend_id');
    }

    public function outgoingRequests()
    {
        return $this->hasMany(FriendRequest::class, 'user_id');
    }

    /**
     * Friend requests, отправленные этим пользователем
     */
    public function sentRequests()
    {
        return $this->hasMany(FriendRequest::class, 'user_id');
    }

    /**
     * Friend requests, полученные этим пользователем
     */
    public function receivedRequests()
    {
        return $this->hasMany(FriendRequest::class, 'friend_id');
    }

    /**
     * Register a new user.
     */
    public static function register(array $data): self
    {
        $user = self::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        Auth::login($user);
        return $user;
    }

    /**
     * Attempt to authenticate a user.
     */
    public static function tryLogin(array $credentials, $request, bool $remember = false): bool
    {
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return true;
        }
        return false;
    }

    /**
     * Logout the user.
     */
    public static function logout($request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Check if user owns a wish list.
     */
    public function ownsWishList(WishList $wishList): bool
    {
        return $this->id === $wishList->user_id;
    }

    /**
     * Check if user has reserved a wish.
     */
    public function hasReservedWish(Wish $wish): bool
    {
        return $this->reservations()->where('wish_id', $wish->id)->exists();
    }

    public function wishes()
    {
        return $this->hasMany(Wish::class);
    }
}
