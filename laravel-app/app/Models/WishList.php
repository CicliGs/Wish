<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\MoneyHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class WishList extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'uuid',
        'is_public',
        'currency',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    /**
     * Default currency for new wish lists.
     */
    public const DEFAULT_CURRENCY = 'USD';

    /**
     * Get supported currencies.
     */
    public static function getSupportedCurrencies(): array
    {
        return array_keys(MoneyHelper::getSupportedCurrencies());
    }

    /**
     * Boot the model and add event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (WishList $wishList) {
            if (empty($wishList->uuid)) {
                $wishList->uuid = (string) Str::uuid();
            }
        });

        static::deleting(function (WishList $wishList) {
            $wishList->wishes()->delete();

            $wishList->wishes()->each(function ($wish) {
                if ($wish->reservation) {
                    $wish->reservation->delete();
                }
            });
        });
    }

    /**
     * Get the wishes for this wish list.
     */
    public function wishes(): HasMany
    {
        return $this->hasMany(Wish::class);
    }

    /**
     * Get the user that owns this wish list.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for public wish lists.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->whereNotNull('uuid');
    }

    /**
     * Get public URL attribute.
     */
    public function getPublicUrlAttribute(): string
    {
        return route('wish-lists.public', $this->uuid);
    }

    /**
     * Check if wish list has wishes.
     */
    public function hasWishes(): bool
    {
        return $this->wishes()->exists();
    }

    /**
     * Get wishes count attribute.
     */
    public function getWishesCountAttribute(): int
    {
        return $this->wishes()->count();
    }

    /**
     * Get reserved wishes count attribute.
     */
    public function getReservedWishesCountAttribute(): int
    {
        return $this->wishes()->where('is_reserved', true)->count();
    }
}
