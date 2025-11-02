<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;


interface FriendRequestRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find friend request by ID or fail
     */
    public function findByIdOrFail(int $id): object;

    /**
     * Find pending request between sender and receiver
     */
    public function findPendingBetween(int $senderId, int $receiverId): ?object;

    /**
     * Find any request between two users
     */
    public function findBetween(int $userId1, int $userId2): ?object;

    /**
     * Delete all requests between two users
     */
    public function deleteBetween(int $userId1, int $userId2): void;

    /**
     * Find accepted friend requests for user
     * 
     * @return array<object>
     */
    public function findAcceptedForUser(object $user): array;

    /**
     * Find incoming pending requests for receiver
     * 
     * @return array<object>
     */
    public function findIncomingPendingForReceiver(object $receiver): array;

    /**
     * Find outgoing pending requests for sender
     * 
     * @return array<object>
     */
    public function findOutgoingPendingForSender(object $sender): array;

    /**
     * Update request status
     */
    public function updateStatus(object $request, string $status): object;

    /**
     * Count accepted friend requests for user
     */
    public function countAcceptedForUser(object $user): int;

    /**
     * Count pending incoming requests for receiver
     */
    public function countPendingIncomingForReceiver(object $receiver): int;
}

