<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model and add event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (WishList $wishList) {
            if (empty($wishList->public_id)) {
                $wishList->public_id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(Wish::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->whereNotNull('public_id');
    }

    public function getPublicUrlAttribute(): string
    {
        return route('wish-lists.public', $this->public_id);
    }

    public function hasWishes(): bool
    {
        return $this->wishes()->exists();
    }

    public function getWishesCountAttribute(): int
    {
        return $this->wishes()->count();
    }

    public function getReservedWishesCountAttribute(): int
    {
        return $this->wishes()->where('is_reserved', true)->count();
    }
}
