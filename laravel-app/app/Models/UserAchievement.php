<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserAchievement
 *
 * @property int $id
 * @property int $user_id
 * @property string $achievement_key
 * @property Carbon|null $received_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @method static Builder|UserAchievement newModelQuery()
 * @method static Builder|UserAchievement newQuery()
 * @method static Builder|UserAchievement query()
 * @method static Builder|UserAchievement where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder|UserAchievement whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|UserAchievement whereNotIn($column, $values, $boolean = 'and')
 * @method static Builder|UserAchievement orWhere($column, $operator = null, $value = null)
 * @method static Builder|UserAchievement find($id, $columns = ['*'])
 * @method static UserAchievement findOrFail($id, $columns = ['*'])
 * @method static Builder|UserAchievement whereUserId($value)
 * @method static Builder|UserAchievement whereAchievementKey($value)
 * @method static Builder|UserAchievement whereReceivedAt($value)
 * @method static Builder|UserAchievement whereCreatedAt($value)
 * @method static Builder|UserAchievement whereUpdatedAt($value)
 */
class UserAchievement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'achievement_key',
        'received_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'received_at' => 'datetime',
    ];

    /**
     * Get the user that owns the achievement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
