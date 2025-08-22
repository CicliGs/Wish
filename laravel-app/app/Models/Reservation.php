<?php

declare(strict_types=1);

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Reservation
 *
 * @property int $id
 * @property int $wish_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Wish $wish
 * @property-read User $user
 * @method static Builder|Reservation newModelQuery()
 * @method static Builder|Reservation newQuery()
 * @method static Builder|Reservation query()
 * @method static Builder|Reservation where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder|Reservation whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|Reservation whereNotIn($column, $values, $boolean = 'and')
 * @method static Builder|Reservation orWhere($column, $operator = null, $value = null)
 * @method static Builder|Reservation find($id, $columns = ['*'])
 * @method static Reservation findOrFail($id, $columns = ['*'])
 * @method static Builder|Reservation whereWishId($value)
 * @method static Builder|Reservation whereUserId($value)
 * @method static Builder|Reservation whereCreatedAt($value)
 * @method static Builder|Reservation whereUpdatedAt($value)
 * @method static whereHas(string $string, Closure $param)
 * @method static create(array $array)
 */
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
}
