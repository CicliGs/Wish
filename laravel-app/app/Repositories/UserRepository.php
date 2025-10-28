<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\DTOs\UserStatisticsDTO;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * User repository implementation
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Create a new repository instance
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find users by name pattern
     */
    public function findByName(string $name): Collection
    {
        return $this->model->where('name', 'like', "%{$name}%")->get();
    }

    /**
     * Find friends of user
     */
    public function findFriends(User $user): Collection
    {
        return $user->friends()->get();
    }

    /**
     * Find users who are friends with given user
     */
    public function findFriendsOf(User $user): Collection
    {
        return $user->friendOf()->get();
    }

    /**
     * Check if two users are friends
     */
    public function areFriends(User $user1, User $user2): bool
    {
        return $user1->friends()->where('friend_id', $user2->id)->exists();
    }

    /**
     * Get user statistics
     */
    public function getStatistics(User $user): UserStatisticsDTO
    {
        return new UserStatisticsDTO(
            totalWishLists: $user->wishLists()->count(),
            totalWishes: $user->wishLists()->withCount('wishes')->get()->sum('wishes_count'),
            totalReservations: $user->reservations()->count(),
            totalFriends: 0,
        );
    }
}
