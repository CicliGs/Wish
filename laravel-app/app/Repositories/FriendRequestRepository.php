<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\FriendRequestStatus;
use App\Models\FriendRequest;
use App\Models\User;
use App\Repositories\Contracts\FriendRequestRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

/**
 * Repository for managing friend request operations.
 */
final class FriendRequestRepository extends BaseRepository implements FriendRequestRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(FriendRequest $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a friend request by ID or throw an exception if not found.
     */
    public function findByIdOrFail(int $id): object
    {
        $request = $this->findById($id);

        if (!($request instanceof FriendRequest)) {
            throw new ModelNotFoundException("FriendRequest not found with ID: {$id}");
        }

        return $request;
    }

    /**
     * Find a pending friend request between sender and receiver.
     */
    public function findPendingBetween(int $senderId, int $receiverId): ?object
    {
        return $this->model
            ->where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->first();
    }

    /**
     * Find any friend request between two users (regardless of direction).
     */
    public function findBetween(int $userId1, int $userId2): ?object
    {
        return $this->model
            ->where(function ($query) use ($userId1, $userId2) {
                $query->where('sender_id', $userId1)
                      ->where('receiver_id', $userId2);
            })
            ->orWhere(function ($query) use ($userId1, $userId2) {
                $query->where('sender_id', $userId2)
                      ->where('receiver_id', $userId1);
            })
            ->first();
    }

    /**
     * Delete all friend requests between two users (regardless of direction).
     */
    public function deleteBetween(int $userId1, int $userId2): void
    {
        $this->model
            ->where(function ($query) use ($userId1, $userId2) {
                $query->where('sender_id', $userId1)
                      ->where('receiver_id', $userId2);
            })
            ->orWhere(function ($query) use ($userId1, $userId2) {
                $query->where('sender_id', $userId2)
                      ->where('receiver_id', $userId1);
            })
            ->delete();
    }

    /**
     * Find all accepted friend requests for a user.
     * Returns requests where the user is either sender or receiver.
     */
    public function findAcceptedForUser(object $user): array
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model
            ->where('status', FriendRequestStatus::ACCEPTED->value)
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->get()
            ->all();
    }

    /**
     * Find all incoming pending friend requests for a receiver.
     */
    public function findIncomingPendingForReceiver(object $receiver): array
    {
        if (!$receiver instanceof User) {
            throw new InvalidArgumentException('Receiver must be an instance of ' . User::class);
        }
        return $this->model
            ->where('receiver_id', $receiver->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->get()
            ->all();
    }

    /**
     * Find all outgoing pending friend requests for a sender.
     */
    public function findOutgoingPendingForSender(object $sender): array
    {
        if (!$sender instanceof User) {
            throw new InvalidArgumentException('Sender must be an instance of ' . User::class);
        }
        return $this->model
            ->where('sender_id', $sender->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->get()
            ->all();
    }

    /**
     * Update the status of a friend request.
     */
    public function updateStatus(object $request, string $status): object
    {
        if (!$request instanceof FriendRequest) {
            throw new InvalidArgumentException('Request must be an instance of ' . FriendRequest::class);
        }
        return $this->update($request, ['status' => $status]);
    }

    /**
     * Count all accepted friend requests for a user.
     * Counts requests where the user is either sender or receiver.
     */
    public function countAcceptedForUser(object $user): int
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model
            ->where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->count();
    }

    /**
     * Count all pending incoming friend requests for a receiver.
     */
    public function countPendingIncomingForReceiver(object $receiver): int
    {
        if (!$receiver instanceof User) {
            throw new InvalidArgumentException('Receiver must be an instance of ' . User::class);
        }
        return $this->model
            ->where('receiver_id', $receiver->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->count();
    }
}

