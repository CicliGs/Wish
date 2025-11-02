<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\FriendsDTO;
use App\DTOs\FriendsSearchDTO;
use App\Enums\FriendRequestStatus;
use App\Models\User;
use App\Models\FriendRequest;
use App\Repositories\Contracts\FriendRequestRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

class FriendService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CacheManagerService $cacheManager,
        private readonly ConnectionInterface $db,
        private readonly FriendRequestRepositoryInterface $friendRequestRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Send friend request.
     *
     * @throws RuntimeException
     */
    public function sendRequest(User $sender, User $receiver): void
    {
        if ($sender->id === $receiver->id) {
            throw new RuntimeException(__('messages.cannot_add_self_as_friend'));
        }

        $existingRequest = $this->friendRequestRepository->findPendingBetween($sender->id, $receiver->id);

        if ($existingRequest) {
            throw new RuntimeException(__('messages.friend_request_already_sent'));
        }

        $existingFriendship = $this->friendRequestRepository->findBetween($sender->id, $receiver->id);
        if ($existingFriendship instanceof FriendRequest && isset($existingFriendship->status) && $existingFriendship->status === FriendRequestStatus::ACCEPTED->value) {
            throw new RuntimeException(__('messages.already_friends'));
        }

        $this->friendRequestRepository->deleteBetween($sender->id, $receiver->id);

        $this->friendRequestRepository->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => FriendRequestStatus::PENDING->value,
        ]);

        $this->cacheManager->clearFriendshipCache($sender->id, $receiver->id);
    }

    /**
     * Accept friend request.
     */
    public function acceptRequest(int $requestId, int $receiverId): void
    {
        $request = $this->friendRequestRepository->findByIdOrFail($requestId);
        
        if (!($request instanceof FriendRequest)) {
            throw new RuntimeException(__('messages.friend_request_not_found'));
        }

        if (isset($request->receiver_id) && $request->receiver_id !== $receiverId) {
            throw new RuntimeException(__('messages.access_denied'));
        }

        $senderId = $request->sender_id ?? 0;
        $receiverRequestId = $request->receiver_id ?? 0;

        $this->db->transaction(function () use ($request, $senderId, $receiverRequestId) {
            $this->friendRequestRepository->updateStatus($request, FriendRequestStatus::ACCEPTED->value);

            $reverseRequest = $this->friendRequestRepository->findBetween($receiverRequestId, $senderId);
            if ($reverseRequest instanceof FriendRequest) {
                $this->friendRequestRepository->updateStatus($reverseRequest, FriendRequestStatus::ACCEPTED->value);
            }
        });

        $this->cacheManager->clearFriendshipCache($senderId, $receiverRequestId);
    }

    /**
     * Decline friend request.
     */
    public function declineRequest(int $requestId, int $receiverId): void
    {
        $request = $this->friendRequestRepository->findByIdOrFail($requestId);
        
        if (!($request instanceof FriendRequest)) {
            throw new RuntimeException(__('messages.friend_request_not_found'));
        }

        if (isset($request->receiver_id) && $request->receiver_id !== $receiverId) {
            throw new RuntimeException(__('messages.access_denied'));
        }

        $this->friendRequestRepository->updateStatus($request, FriendRequestStatus::DECLINED->value);
        $senderId = $request->sender_id ?? 0;
        $receiverRequestId = $request->receiver_id ?? 0;
        $this->cacheManager->clearFriendshipCache($senderId, $receiverRequestId);
    }

    /**
     * Remove friendship.
     */
    public function removeFriendship(User $user, User $friend): void
    {
        $this->friendRequestRepository->deleteBetween($user->id, $friend->id);
        $this->cacheManager->clearFriendshipCache($user->id, $friend->id);
    }

    /**
     * Get user's friends list.
     *
     * @return Collection<int, User>
     */
    public function getFriends(User $user): Collection
    {
        return collect($this->friendRequestRepository->findAcceptedForUser($user))
            ->map(function ($request) use ($user) {
                if (!($request instanceof FriendRequest)) {
                    return null;
                }
                $senderId = $request->sender_id ?? 0;
                $receiverId = $request->receiver_id ?? 0;
                $friendId = $senderId === $user->id ? $receiverId : $senderId;
                return $this->userRepository->findById($friendId);
            })
            ->filter(fn($friend) => $friend instanceof User)
            ->unique('id')
            ->values();
    }

    /**
     * Get incoming requests.
     */
    public function getIncomingRequests(User $receiver): Collection
    {
        return collect($this->friendRequestRepository->findIncomingPendingForReceiver($receiver));
    }

    /**
     * Get outgoing requests.
     */
    public function getOutgoingRequests(User $sender): Collection
    {
        return collect($this->friendRequestRepository->findOutgoingPendingForSender($sender));
    }

    /**
     * Get page data.
     */
    public function getPageData(User $user, ?int $selectedFriendId = null): FriendsDTO
    {
        $friends = $this->getFriends($user);

        $selectedFriend = $selectedFriendId ? $friends->firstWhere('id', $selectedFriendId) : null;

        return FriendsDTO::fromFriendsData(
            $friends->all(),
            $this->getIncomingRequests($user)->all(),
            $this->getOutgoingRequests($user)->all(),
            $selectedFriend
        );
    }

    /**
     * Search with status.
     */
    public function searchWithStatus(string $searchTerm, User $currentUser): FriendsSearchDTO
    {
        $searchTerm = trim($searchTerm);

        if (empty($searchTerm)) {
            return FriendsSearchDTO::empty();
        }

        $users = $this->userRepository->searchByNameOrEmail($searchTerm, $currentUser->id, 10);

        $friendStatuses = collect($users)->mapWithKeys(function ($user) use ($currentUser) {
            if (!($user instanceof User)) {
                return [];
            }
            return [$user->id => $this->getStatusBetween($currentUser, $user->id)];
        })->filter();

        return FriendsSearchDTO::fromSearchResults($users, $searchTerm, $friendStatuses->toArray());
    }

    /**
     * Get status between users.
     */
    private function getStatusBetween(User $currentUser, int $otherUserId): string
    {
        $request = $this->friendRequestRepository->findBetween($currentUser->id, $otherUserId);

        if (!($request instanceof FriendRequest)) {
            return 'none';
        }

        $status = $request->status ?? null;
        $senderId = $request->sender_id ?? 0;

        return match ($status) {
            FriendRequestStatus::ACCEPTED->value => 'friends',
            FriendRequestStatus::PENDING->value => $senderId === $currentUser->id ? 'request_sent' : 'request_received',
            default => 'none'
        };
    }
}
