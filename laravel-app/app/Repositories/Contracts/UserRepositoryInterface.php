<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\UserStatisticsDTO;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?object;

    /**
     * Find users by name pattern
     *
     * @return array<object>
     */
    public function findByName(string $name): array;

    /**
     * Find friends of user
     *
     * @return array<object>
     */
    public function findFriends(object $user): array;

    /**
     * Find users who are friends with given user
     *
     * @return array<object>
     */
    public function findFriendsOf(object $user): array;

    /**
     * Check if two users are friends
     */
    public function areFriends(object $user1, object $user2): bool;

    /**
     * Get user statistics
     */
    public function getStatistics(object $user): UserStatisticsDTO;

    /**
     * Search users by name or email, excluding current user
     *
     * @return array<object>
     */
    public function searchByNameOrEmail(string $searchTerm, int $excludeUserId, int $limit = 10): array;
}
