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
use Symfony\Component\HttpKernel\Exception\HttpException;

class FriendService
{
    public function __construct(
        protected CacheManagerService $cacheManager
    ) {}

    /**
     * Send friend request from one user to another.
     */
    public function sendFriendRequestToUser(User $sender, int $receiverId): bool|string
    {
        if ($sender->id === $receiverId) {
            return __('messages.cannot_add_self_as_friend');
        }

        $this->deleteAllFriendRequestsBetweenUsers($sender->id, $receiverId);

        FriendRequest::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'status' => FriendRequestStatus::PENDING->value,
        ]);

        $this->cacheManager->clearFriendshipCache($sender->id, $receiverId);

        return true;
    }

    /**
     * Accept friend request by request ID.
     */
    public function acceptFriendRequestById(int $requestId, int $receiverId): void
    {
        $request = FriendRequest::findOrFail($requestId);

        if ($request->receiver_id !== $receiverId) {
            throw new HttpException(403, __('messages.access_denied'));
        }

        DB::transaction(function () use ($request) {
            $request->update(['status' => FriendRequestStatus::ACCEPTED->value]);

            $reverseRequest = $this->findExistingFriendRequestBetweenUsers($request->receiver_id, $request->sender_id);
            if ($reverseRequest) {
                $reverseRequest->update(['status' => FriendRequestStatus::ACCEPTED->value]);
            }
        });

        $this->cacheManager->clearFriendshipCache($request->sender_id, $request->receiver_id);
    }

    /**
     * Decline friend request by request ID.
     */
    public function declineFriendRequestById(int $requestId, int $receiverId): void
    {
        $request = FriendRequest::findOrFail($requestId);

        if ($request->receiver_id !== $receiverId) {
            throw new HttpException(403, __('messages.access_denied'));
        }

        $request->update(['status' => FriendRequestStatus::DECLINED->value]);
        $this->cacheManager->clearFriendshipCache($request->sender_id, $request->receiver_id);
    }

    /**
     * Remove friendship between two users.
     */
    public function removeFriendshipBetweenUsers(User $user, int $friendId): void
    {
        $this->deleteAllFriendRequestsBetweenUsers($user->id, $friendId);
        $this->cacheManager->clearFriendshipCache($user->id, $friendId);
    }

    /**
     * Get user's friends list.
     */
    /**
     * @return Collection<int, User>
     */
    public function getFriendsForUser(User $user): Collection
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
     * Get incoming friend requests (requests received by the user).
     */
    public function getIncomingFriendRequests(User $receiver): Collection
    {
        return FriendRequest::where('receiver_id', $receiver->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->with('sender')
            ->get();
    }

    /**
     * Get outgoing friend requests (requests sent by the user).
     */
    public function getOutgoingFriendRequests(User $sender): Collection
    {
        return FriendRequest::where('sender_id', $sender->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->with('receiver')
            ->get();
    }

    /**
     * Get friends page data.
     */
    public function getFriendsPageData(User $user, ?int $selectedFriendId = null): FriendsDTO
    {
        $friends = $this->getFriendsForUser($user);

        $selectedFriend = $selectedFriendId ? $friends->firstWhere('id', $selectedFriendId) : null;

        return FriendsDTO::fromFriendsData($friends, $this->getIncomingFriendRequests($user), $this->getOutgoingFriendRequests($user), $selectedFriend);
    }

    /**
     * Search friends with status and return DTO.
     */
    public function searchUsersWithFriendStatus(string $searchTerm, User $currentUser): FriendsSearchDTO
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
            $user->id => $this->getFriendshipStatusBetweenUsers($currentUser, $user->id)
        ]);

        return FriendsSearchDTO::fromSearchResults($users, $searchTerm, $friendStatuses->toArray());
    }

    /**
     * Get friendship status between users.
     */
    private function getFriendshipStatusBetweenUsers(User $currentUser, int $otherUserId): string
    {
        $request = $this->findExistingFriendRequestBetweenUsers($currentUser->id, $otherUserId);

        return match ($request?->status) {
            FriendRequestStatus::ACCEPTED->value => 'friends',
            FriendRequestStatus::PENDING->value => $request->sender_id === $currentUser->id ? 'request_sent' : 'request_received',
            default => 'none'
        };
    }

    /**
     * Find existing friend request between users.
     */
    private function findExistingFriendRequestBetweenUsers(int $senderId, int $receiverId): ?FriendRequest
    {
        return FriendRequest::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                  ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)
                  ->where('receiver_id', $senderId);
        })->first();
    }

    /**
     * Delete all friend requests between two users.
     */
    private function deleteAllFriendRequestsBetweenUsers(int $userId1, int $userId2): void
    {
        FriendRequest::where(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId1)
                  ->where('receiver_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('sender_id', $userId2)
                  ->where('receiver_id', $userId1);
        })->delete();
    }
}
