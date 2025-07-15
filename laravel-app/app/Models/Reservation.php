<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'wish_id',
        'user_id',
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
     * Get the wish that is reserved.
     */
    public function wish(): BelongsTo
    {
        return $this->belongsTo(Wish::class);
    }

    /**
     * Get the user who made the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wish list that contains the reserved wish.
     */
    public function wishList(): BelongsTo
    {
        return $this->belongsTo(WishList::class, 'wish_list_id', 'id', 'wish');
    }

    /**
     * Check if the reservation is still active.
     */
    public function isActive(): bool
    {
        return $this->wish && $this->wish->is_reserved;
    }
    //
    //    public function getFormattedReservedAtAttribute(): string
    //    {
    //        return $this->created_at->format('d.m.Y H:i');
    //    }
}
