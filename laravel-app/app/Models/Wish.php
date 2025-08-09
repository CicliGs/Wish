<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Wish extends Model
{
    use HasFactory;

    private const DECIMAL_PLACES = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
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

    /**
     * Get the user that owns the wish list.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id', 'wish_list');
    }

    /**
     * Get the reservation for this wish.
     */
    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class);
    }

    /**
     * Get the wish list that owns the wish.
     */
    public function wishList(): BelongsTo
    {
        return $this->belongsTo(WishList::class);
    }

    /**
     * Scope for wish list.
     */
    public function scopeForWishList(Builder $query, int $wishListId): Builder
    {
        return $query->where('wish_list_id', $wishListId);
    }

    /**
     * Scope for reserved wishes.
     */
    public function scopeReserved(Builder $query): Builder
    {
        return $query->where('is_reserved', true);
    }

    /**
     * Scope for available wishes.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_reserved', false);
    }

    /**
     * Get formatted price attribute.
     */
    public function getFormattedPriceAttribute(): string
    {
        if (!$this->price || $this->price == 0) {
            return '';
        }

        $currency = $this->getCurrentUserCurrency();
        $price = $this->getPriceAsFloat();
        return number_format($price, self::DECIMAL_PLACES) . ' ' . $currency;
    }

    /**
     * Get formatted price for specific user.
     */
    public function getFormattedPriceForUser(?User $user = null): string
    {
        if (!$this->price || $this->price == 0) {
            return '';
        }

        $currency = $user ? $user->currency : $this->getCurrentUserCurrency();
        $price = $this->getPriceAsFloat();
        return number_format($price, self::DECIMAL_PLACES) . ' ' . $currency;
    }

    /**
     * Get price as float value.
     */
    private function getPriceAsFloat(): float
    {
        // Handle null or empty values
        if (empty($this->price) || $this->price === null) {
            return 0.0;
        }

        // If it's already a numeric value
        if (is_numeric($this->price)) {
            return (float) $this->price;
        }
        
        // If it's a string, try to extract numeric value
        if (is_string($this->price)) {
            // Remove any non-numeric characters except dots and minus
            $cleaned = preg_replace('/[^0-9.-]/', '', $this->price);
            
            // Check if the cleaned string is numeric
            if (is_numeric($cleaned)) {
                $floatValue = (float) $cleaned;
                // Ensure the value is reasonable (not negative for price)
                return $floatValue >= 0 ? $floatValue : 0.0;
            }
        }

        // If it's an object (like from Laravel's decimal cast), try to convert to string first
        if (is_object($this->price)) {
            $stringValue = (string) $this->price;
            if (is_numeric($stringValue)) {
                return (float) $stringValue;
            }
        }
        
        return 0.0;
    }

    /**
     * Get current user currency.
     */
    private function getCurrentUserCurrency(): string
    {
        if (auth()->check()) {
            return auth()->user()->currency;
        }

        return User::DEFAULT_CURRENCY;
    }

    /**
     * Check if wish is available.
     */
    public function isAvailable(): bool
    {
        return !$this->is_reserved;
    }

    /**
     * Check if wish has reservation.
     */
    public function hasReservation(): bool
    {
        return $this->is_reserved && $this->reservation()->exists();
    }

    /**
     * Get user who reserved this wish.
     */
    public function getReservedByUser(): ?User
    {
        if (!$this->reservation) {
            return null;
        }
        
        return $this->reservation->user;
    }

    /**
     * Reserve wish for user.
     */
    public function reserveForUser(int $userId): bool
    {
        if ($this->is_reserved) {
            return false;
        }

        $this->reservation()->create(['user_id' => $userId]);
        $this->update(['is_reserved' => true]);

        return true;
    }

    /**
     * Remove reservation from wish.
     */
    public function dereserve(): bool
    {
        if (!$this->is_reserved) {
            return false;
        }

        $this->reservation()->delete();
        $this->update(['is_reserved' => false]);

        return true;
    }

    /**
     * Resolve route binding.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (request()->route('wishList')) {
            $wishListId = request()->route('wishList');
            if (is_numeric($wishListId)) {
                return $this->where('id', $value)
                    ->where('wish_list_id', $wishListId)
                    ->first();
            }
        }
        return $this->where('id', $value)->first();
    }
}
