<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Wish extends Model
{
    use HasFactory;

    protected $fillable = [
        'wish_list_id',
        'title',
        'url',
        'image',
        'price',
        'is_reserved',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_reserved' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id', 'wish_list');
    }

    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class);
    }

    public function wishList(): BelongsTo
    {
        return $this->belongsTo(WishList::class);
    }

    public function scopeForWishList(Builder $query, int $wishListId): Builder
    {
        return $query->where('wish_list_id', $wishListId);
    }

    public function scopeReserved(Builder $query): Builder
    {
        return $query->where('is_reserved', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_reserved', false);
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->price ? number_format($this->price, 2).' BYN' : '';
    }

    public function isAvailable(): bool
    {
        return ! $this->is_reserved;
    }

    public function hasReservation(): bool
    {
        return $this->reservation()->exists();
    }

    public function getReservedByUser(): ?User
    {
        return $this->reservation?->user;
    }

    public function reserveForUser(int $userId): bool
    {
        if ($this->is_reserved) {
            return false;
        }

        $this->reservation()->create(['user_id' => $userId]);
        $this->update(['is_reserved' => true]);

        return true;
    }

    public function dereserve(): bool
    {
        if (! $this->is_reserved) {
            return false;
        }

        $this->reservation()->delete();
        $this->update(['is_reserved' => false]);

        return true;
    }
}
