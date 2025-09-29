<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    /** @phpstan-ignore-next-line */
    public string $friend_id;
    /** @phpstan-ignore-next-line */
    public bool $is_read;
    /** @phpstan-ignore-next-line */
    public \DateTimeInterface $created_at;
    /** @phpstan-ignore-next-line */
    public \DateTimeInterface $updated_at;

    protected $fillable = [
        'user_id',
        'friend_id',
        'wish_id',
        'message',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Friend
     */
    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    /**
     * Gift
     */
    public function wish(): BelongsTo
    {
        return $this->belongsTo(Wish::class);
    }
}
