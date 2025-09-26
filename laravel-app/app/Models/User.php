<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\MoneyHelper;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const DEFAULT_CURRENCY = 'USD';

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
     * Get incoming friend requests.
     */
    public function incomingRequests(): HasMany
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

}
