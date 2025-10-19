<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\FriendsDTO;
use App\DTOs\FriendsSearchDTO;
use App\Enums\FriendRequestStatus;
use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FriendService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CacheManagerService $cacheManager
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

        $this->deleteBetween($sender->id, $receiver->id);

        FriendRequest::create([
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
        $request = FriendRequest::findOrFail($requestId);

        if ($request->receiver_id !== $receiverId) {
            throw new HttpException(403, __('messages.access_denied'));
        }

        DB::transaction(function () use ($request) {
            $request->update(['status' => FriendRequestStatus::ACCEPTED->value]);

            $reverseRequest = $this->findBetween($request->receiver_id, $request->sender_id);
            if ($reverseRequest) {
                $reverseRequest->update(['status' => FriendRequestStatus::ACCEPTED->value]);
            }
        });

        $this->cacheManager->clearFriendshipCache($request->sender_id, $request->receiver_id);
    }

    /**
     * Decline friend request.
     */
    public function declineRequest(int $requestId, int $receiverId): void
    {
        $request = FriendRequest::findOrFail($requestId);

        if ($request->receiver_id !== $receiverId) {
            throw new HttpException(403, __('messages.access_denied'));
        }

        $request->update(['status' => FriendRequestStatus::DECLINED->value]);
        $this->cacheManager->clearFriendshipCache($request->sender_id, $request->receiver_id);
    }

    /**
     * Remove friendship.
     */
    public function removeFriendship(User $user, User $friend): void
    {
        $this->deleteBetween($user->id, $friend->id);
        $this->cacheManager->clearFriendshipCache($user->id, $friend->id);
    }

    /**
     * Get user's friends list.
     *
     * @return Collection<int, User>
     */
    public function getFriends(User $user): Collection
    {
        return FriendRequest::where('status', FriendRequestStatus::ACCEPTED->value)
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->with(['sender', 'receiver'])
            ->get()
            ->map(function ($request) use ($user) {
                return $request->sender_id === $user->id ? $request->receiver : $request->sender;
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
        return FriendRequest::where('receiver_id', $receiver->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->with('sender')
            ->get();
    }

    /**
     * Get outgoing requests.
     */
    public function getOutgoingRequests(User $sender): Collection
    {
        return FriendRequest::where('sender_id', $sender->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->with('receiver')
            ->get();
    }

    /**
     * Get page data.
     */
    public function getPageData(User $user, ?int $selectedFriendId = null): FriendsDTO
    {
        $friends = $this->getFriends($user);

        $selectedFriend = $selectedFriendId ? $friends->firstWhere('id', $selectedFriendId) : null;

        return FriendsDTO::fromFriendsData($friends, $this->getIncomingRequests($user), $this->getOutgoingRequests($user), $selectedFriend);
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

        $users = User::where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%$searchTerm%")
                      ->orWhere('email', 'like', "%$searchTerm%");
            })
            ->where('id', '!=', $currentUser->id)
            ->limit(10)
            ->get();

        $friendStatuses = $users->mapWithKeys(fn($user) => [
            $user->id => $this->getStatusBetween($currentUser, $user->id)
        ]);

        return FriendsSearchDTO::fromSearchResults($users, $searchTerm, $friendStatuses->toArray());
    }

    /**
     * Get status between users.
     */
    private function getStatusBetween(User $currentUser, int $otherUserId): string
    {
        $request = $this->findBetween($currentUser->id, $otherUserId);

        return match ($request?->status) {
            FriendRequestStatus::ACCEPTED->value => 'friends',
            FriendRequestStatus::PENDING->value => $request->sender_id === $currentUser->id ? 'request_sent' : 'request_received',
            default => 'none'
        };
    }

    /**
     * Build query for requests between two users.
     */
    private function queryBetween(int $userId1, int $userId2)
    {
        return FriendRequest::where(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId1)
                  ->where('receiver_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId2)
                  ->where('receiver_id', $userId1);
        });
    }

    /**
     * Find request between users.
     */
    private function findBetween(int $userId1, int $userId2): ?FriendRequest
    {
        return $this->queryBetween($userId1, $userId2)->first();
    }

    /**
     * Delete all requests between users.
     */
    private function deleteBetween(int $userId1, int $userId2): void
    {
        $this->queryBetween($userId1, $userId2)->delete();
    }
}
