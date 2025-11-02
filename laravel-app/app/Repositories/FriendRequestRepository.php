<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\FriendRequestStatus;
use App\Models\FriendRequest;
use App\Models\User;
use App\Repositories\Contracts\FriendRequestRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class FriendRequestRepository extends BaseRepository implements FriendRequestRepositoryInterface
{
    public function __construct(FriendRequest $model)
    {
        parent::__construct($model);
    }

    public function findByIdOrFail(int $id): object
    {
        $request = $this->findById($id);
        
        if (!$request || !($request instanceof FriendRequest)) {
            throw new ModelNotFoundException("FriendRequest not found with ID: {$id}");
        }
        
        return $request;
    }

    public function findPendingBetween(int $senderId, int $receiverId): ?object
    {
        return $this->model
            ->where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->first();
    }

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
     * @return array<object>
     */
    public function findAcceptedForUser(object $user): array
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
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
     * @return array<object>
     */
    public function findIncomingPendingForReceiver(object $receiver): array
    {
        if (!$receiver instanceof User) {
            throw new \InvalidArgumentException('Receiver must be an instance of ' . User::class);
        }
        return $this->model
            ->where('receiver_id', $receiver->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->get()
            ->all();
    }

    /**
     * @return array<object>
     */
    public function findOutgoingPendingForSender(object $sender): array
    {
        if (!$sender instanceof User) {
            throw new \InvalidArgumentException('Sender must be an instance of ' . User::class);
        }
        return $this->model
            ->where('sender_id', $sender->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->get()
            ->all();
    }

    public function updateStatus(object $request, string $status): object
    {
        if (!$request instanceof FriendRequest) {
            throw new \InvalidArgumentException('Request must be an instance of ' . FriendRequest::class);
        }
        return $this->update($request, ['status' => $status]);
    }

    public function countAcceptedForUser(object $user): int
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model
            ->where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->count();
    }

    public function countPendingIncomingForReceiver(object $receiver): int
    {
        if (!$receiver instanceof User) {
            throw new \InvalidArgumentException('Receiver must be an instance of ' . User::class);
        }
        return $this->model
            ->where('receiver_id', $receiver->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->count();
    }
}

