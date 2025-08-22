<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\FriendRequest
 *
 * @property int $id
 * @property int $user_id
 * @property int $receiver_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $sender
 * @property-read User $receiver
 * @method static Builder|FriendRequest newModelQuery()
 * @method static Builder|FriendRequest newQuery()
 * @method static Builder|FriendRequest query()
 * @method static Builder|FriendRequest where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder|FriendRequest whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|FriendRequest whereNotIn($column, $values, $boolean = 'and')
 * @method static Builder|FriendRequest orWhere($column, $operator = null, $value = null)
 * @method static Builder|FriendRequest find($id, $columns = ['*'])
 * @method static FriendRequest findOrFail($id, $columns = ['*'])
 * @method static Builder|FriendRequest whereUserId($value)
 * @method static Builder|FriendRequest whereReceiverId($value)
 * @method static Builder|FriendRequest whereStatus($value)
 * @method static Builder|FriendRequest whereCreatedAt($value)
 * @method static Builder|FriendRequest whereUpdatedAt($value)
 */
class FriendRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'receiver_id',
        'status',
    ];

    /**
     * Get the sender of this friend request.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the receiver of this friend request.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
