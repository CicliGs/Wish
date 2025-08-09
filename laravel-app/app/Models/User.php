<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const DEFAULT_CURRENCY = 'BYN';
    private const SUPPORTED_CURRENCIES = ['BYN', 'USD', 'EUR', 'RUB'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'currency',
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
    public function wishLists(): HasMany
    {
        return $this->hasMany(WishList::class);
    }

    /**
     * Get the reservations for the user.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get friends added by this user.
     */
    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id');
    }

    /**
     * Get users who added this user as friend.
     */
    public function friendOf(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friends', 'friend_id', 'user_id');
    }

    /**
     * Get incoming friend requests.
     */
    public function incomingRequests(): HasMany
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id');
    }

    /**
     * Get outgoing friend requests.
     */
    public function outgoingRequests(): HasMany
    {
        return $this->hasMany(FriendRequest::class, 'user_id');
    }

    /**
     * Get sent friend requests.
     */
    public function sentRequests(): HasMany
    {
        return $this->hasMany(FriendRequest::class, 'user_id');
    }

    /**
     * Get received friend requests.
     */
    public function receivedRequests(): HasMany
    {
        return $this->hasMany(FriendRequest::class, 'receiver_id');
    }

    /**
     * Register a new user.
     */
    public static function register(array $data): self
    {
        $data['password'] = Hash::make($data['password']);
        $data['currency'] = $data['currency'] ?? self::DEFAULT_CURRENCY;
        
        return self::create($data);
    }

    /**
     * Try to login user.
     */
    public static function tryLogin(array $credentials, Request $request, bool $remember = false): bool
    {
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return true;
        }
        
        return false;
    }

    /**
     * Logout user.
     */
    public static function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Check if user owns wish list.
     */
    public function ownsWishList(WishList $wishList): bool
    {
        return $this->id === $wishList->user_id;
    }

    /**
     * Check if user has reserved wish.
     */
    public function hasReservedWish(Wish $wish): bool
    {
        return $wish->reservation && $wish->reservation->user_id === $this->id;
    }

    /**
     * Get user wishes through wish lists.
     */
    public function wishes(): HasManyThrough
    {
        return $this->hasManyThrough(Wish::class, WishList::class);
    }

    /**
     * Get user achievements.
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Check if user has achievement.
     */
    public function hasAchievement(string $achievementKey): bool
    {
        return $this->achievements()->where('achievement_key', $achievementKey)->exists();
    }

    /**
     * Get user's preferred currency.
     */
    public function getCurrencyAttribute(): string
    {
        return $this->attributes['currency'] ?? self::DEFAULT_CURRENCY;
    }

    /**
     * Set user's preferred currency.
     */
    public function setCurrencyAttribute(string $currency): void
    {
        $this->attributes['currency'] = in_array($currency, self::SUPPORTED_CURRENCIES) 
            ? $currency 
            : self::DEFAULT_CURRENCY;
    }

    /**
     * Get supported currencies.
     */
    public static function getSupportedCurrencies(): array
    {
        return self::SUPPORTED_CURRENCIES;
    }

    /**
     * Check if currency is supported.
     */
    public static function isCurrencySupported(string $currency): bool
    {
        return in_array($currency, self::SUPPORTED_CURRENCIES);
    }
}
