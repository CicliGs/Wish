<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\DTOs\UserStatisticsDTO;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Container\Container;

/**
 * User repository implementation
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Create a new repository instance
     */
    public function __construct(
        User $model,
        private readonly ConnectionInterface $db,
        private readonly Container $container
    ) {
        parent::__construct($model);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?object
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find users by name pattern
     * @return array<object>
     */
    public function findByName(string $name): array
    {
        return $this->model->where('name', 'like', "%{$name}%")->get()->all();
    }

    /**
     * Find friends of user
     * @return array<object>
     */
    public function findFriends(object $user): array
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model
            ->join('friends', function ($join) use ($user) {
                $join->on('users.id', '=', 'friends.friend_id')
                     ->where('friends.user_id', '=', $user->id);
            })
            ->select('users.*')
            ->get()
            ->all();
    }

    /**
     * Find users who are friends with given user
     * @return array<object>
     */
    public function findFriendsOf(object $user): array
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model
            ->join('friends', function ($join) use ($user) {
                $join->on('users.id', '=', 'friends.user_id')
                     ->where('friends.friend_id', '=', $user->id);
            })
            ->select('users.*')
            ->get()
            ->all();
    }

    /**
     * Check if two users are friends
     */
    public function areFriends(object $user1, object $user2): bool
    {
        if (!$user1 instanceof User || !$user2 instanceof User) {
            throw new \InvalidArgumentException('Users must be instances of ' . User::class);
        }
        return $this->db->table('friends')
            ->where(function ($query) use ($user1, $user2) {
                $query->where('user_id', $user1->id)
                      ->where('friend_id', $user2->id);
            })
            ->orWhere(function ($query) use ($user1, $user2) {
                $query->where('user_id', $user2->id)
                      ->where('friend_id', $user1->id);
            })
            ->exists();
    }

    /**
     * Get user statistics
     */
    public function getStatistics(object $user): UserStatisticsDTO
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        
        // Use lazy resolution to avoid circular dependency
        $wishListRepository = $this->container->make(\App\Repositories\Contracts\WishListRepositoryInterface::class);
        $reservationRepository = $this->container->make(\App\Repositories\Contracts\ReservationRepositoryInterface::class);
        
        $wishListStats = $wishListRepository->getStatistics($user);
        $totalReservations = $reservationRepository->countByUser($user);
        
        return new UserStatisticsDTO(
            totalWishLists: $wishListStats->totalWishLists,
            totalWishes: $wishListStats->totalWishes,
            totalReservations: $totalReservations,
            totalFriends: 0,
        );
    }

    /**
     * Search users by name or email, excluding current user
     * @return array<object>
     */
    public function searchByNameOrEmail(string $searchTerm, int $excludeUserId, int $limit = 10): array
    {
        return $this->model
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%");
            })
            ->where('id', '!=', $excludeUserId)
            ->limit($limit)
            ->get()
            ->all();
    }
}
