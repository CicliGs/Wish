<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use App\DTOs\UserStatisticsDTO;
use Illuminate\Database\Eloquent\Collection;

/**
 * User repository interface
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find users by name pattern
     */
    public function findByName(string $name): Collection;

    /**
     * Find friends of user
     */
    public function findFriends(User $user): Collection;

    /**
     * Find users who are friends with given user
     */
    public function findFriendsOf(User $user): Collection;

    /**
     * Check if two users are friends
     */
    public function areFriends(User $user1, User $user2): bool;

    /**
     * Get user statistics
     */
    public function getStatistics(User $user): UserStatisticsDTO;
}
