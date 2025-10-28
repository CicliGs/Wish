<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\WishObserver;
use App\Support\MoneyHelper;
use Exception;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use Money\Money;

#[ObservedBy(WishObserver::class)]
class Wish extends Model
{
    use HasFactory;

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
        return $this->getFormattedPriceForUser();
    }

    /**
     * Get formatted price for specific user.
     */
    public function getFormattedPriceForUser(?User $user = null): string
    {
        try {
            if (!$this->hasValidPrice()) {
                return '';
            }

            return MoneyHelper::format($this->getMoneyObject());
        } catch (Exception $e) {
            Log::error('Error formatting price', [
                'price' => $this->price ?? 'null',
                'user_id' => $user->id ?? 'null',
                'error' => $e->getMessage()
            ]);

            return '';
        }
    }

    /**
     * Check if wish has valid price.
     */
    private function hasValidPrice(): bool
    {
        if (!isset($this->price) || $this->price === null) {
            return false;
        }


        return $this->getPriceAsFloat() > 0 && is_finite($this->getPriceAsFloat());
    }

    /**
     * Get Money object for this wish.
     */
    private function getMoneyObject(): Money
    {
        return MoneyHelper::create($this->getPriceAsFloat(), $this->getWishListCurrency());
    }

    /**
     * Get price as float value.
     */
    private function getPriceAsFloat(): float
    {
        if ($this->price === null) {
            return 0.0;
        }

        if (is_numeric($this->price)) {
            return (float) $this->price;
        }

        if (is_string($this->price) && $this->price !== '') {
            $cleaned = preg_replace('/[^0-9.-]/', '', $this->price);
            if (is_numeric($cleaned)) {
                $floatValue = (float) $cleaned;
                return $floatValue >= 0 ? $floatValue : 0.0;
            }
        }

        if (is_object($this->price)) {
            $stringValue = (string) $this->price;
            if (is_numeric($stringValue)) {
                return (float) $stringValue;
            }
        }

        return 0.0;
    }

    /**
     * Get wish list currency.
     */
    private function getWishListCurrency(): string
    {
        if ($this->wishList && $this->wishList->currency) {
            return $this->wishList->currency;
        }

        if (auth()->check() && auth()->user()) {
            return auth()->user()->currency;
        }

        return User::DEFAULT_CURRENCY;
    }

    /**
     * Get Money object for this wish (public API).
     */
    public function getMoney(): ?Money
    {
        try {
            if (!$this->hasValidPrice()) {
                return null;
            }

            return $this->getMoneyObject();
        } catch (Exception $e) {
            Log::error('Error creating Money object', [
                'price' => $this->price ?? 'null',
                'error' => $e->getMessage()
            ]);

            return null;
        }
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
     * Check if wish has image.
     */
    public function hasImage(): bool
    {
        return !empty($this->image);
    }

    /**
     * Get image URL attribute.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->hasImage()) {
            return null;
        }

        return $this->image;
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
