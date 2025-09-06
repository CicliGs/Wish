<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\FriendsDTO;
use App\DTOs\FriendsSearchDTO;
use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FriendService
{
    public function __construct(
        protected CacheService $cacheService
    ) {}

    /**
     * Send friend request from one user to another.
     */
    public function sendFriendRequest(User $sender, int $receiverId): bool|string
    {
        if ($this->isSelfRequest($sender->id, $receiverId)) {
            return __('messages.cannot_add_self_as_friend');
        }

        $existingRequest = $this->findExistingRequest($sender->id, $receiverId);

        if ($existingRequest) {
            return $this->handleExistingRequest($existingRequest, $sender, $receiverId);
        }

        return $this->createNewRequest($sender->id, $receiverId);
    }

    /**
     * Accept friend request by request ID.
     */
    public function acceptFriendRequest(int $requestId, int $receiverId): void
    {
        $request = FriendRequest::findOrFail($requestId);

        if (!$this->canAcceptRequest($request, $receiverId)) {
            throw new HttpException(403, __('messages.access_denied'));
        }

        DB::transaction(function () use ($request) {
            $this->acceptRequest($request);
        });

        $this->clearUserCaches($request->sender_id, $request->receiver_id);
    }

    /**
     * Decline friend request by request ID.
     */
    public function declineFriendRequest(int $requestId, int $receiverId): void
    {
        $request = FriendRequest::findOrFail($requestId);

        if (!$this->canDeclineRequest($request, $receiverId)) {
            throw new HttpException(403, __('messages.access_denied'));
        }

        $request->update(['status' => FriendRequestStatus::DECLINED->value]);

        $this->clearUserCaches($request->sender_id, $request->receiver_id);
    }

    /**
     * Remove friendship between two users.
     */
    public function removeFriendship(User $user, int $friendId): void
    {
        DB::transaction(function () use ($user, $friendId) {
            $this->deleteFriendRequests($user->id, $friendId);
            $this->deleteFriendship($user->id, $friendId);
        });

        $this->clearUserCaches($user->id, $friendId);
    }

    /**
     * Get user's friends list.
     */
    public function getFriends(User $user): Collection
    {
        $friendIds = $this->getFriendIds($user->id);

        return User::whereIn('id', $friendIds)->get();
    }

    /**
     * Get incoming friend requests (requests received by the user).
     */
    public function getIncomingRequests(User $receiver): Collection
    {
        return FriendRequest::where('receiver_id', $receiver->id)
            ->where('status', FriendRequestStatus::PENDING->value)
            ->with('sender')
            ->get();
    }

    /**
     * Get outgoing friend requests (requests sent by the user).
     */
    public function getOutgoingRequests(User $sender): Collection
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
        $friends = $this->getFriends($user);
        $incoming = $this->getIncomingRequests($user);
        $outgoing = $this->getOutgoingRequests($user);

        $selectedFriend = $selectedFriendId ? $friends->firstWhere('id', $selectedFriendId) : null;

        return new FriendsDTO(
            friends: $friends,
            incomingRequests: $incoming,
            outgoingRequests: $outgoing,
            selectedFriend: $selectedFriend
        );
    }

    /**
     * Search users with friendship status information.
     * 
     * @param string $searchTerm Search term for name or email
     * @param int $excludeUserId User ID to exclude from search
     * @param User $currentUser Current authenticated user
     * @return Collection Collection of users with friend status
     */
    public function searchUsersWithFriendStatus(string $searchTerm, int $excludeUserId, User $currentUser): Collection
    {
        $users = $this->findUsersBySearchTerm($searchTerm, $excludeUserId);
        
        return $this->enrichUsersWithFriendStatus($users, $currentUser);
    }

    /**
     * Find users by search term.
     * 
     * @param string $searchTerm Search term for name or email
     * @param int $excludeUserId User ID to exclude from search
     * @return Collection Collection of users
     */
    private function findUsersBySearchTerm(string $searchTerm, int $excludeUserId): Collection
    {
        return User::where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%$searchTerm%")
                      ->orWhere('email', 'like', "%$searchTerm%");
            })
            ->where('id', '!=', $excludeUserId)
            ->limit(10)
            ->get();
    }

    /**
     * Enrich users collection with friend status information.
     * 
     * @param Collection $users Collection of users
     * @param User $currentUser Current authenticated user
     * @return Collection Collection of users with friend status
     */
    private function enrichUsersWithFriendStatus(Collection $users, User $currentUser): Collection
    {
        return $users->map(function ($user) use ($currentUser) {
            $user->friend_status = $this->getFriendStatus($currentUser, $user->id);
            return $user;
        });
    }

    /**
     * Search friends with status and return DTO.
     */
    public function searchFriendsWithStatus(string $searchTerm, User $currentUser): FriendsSearchDTO
    {
        $searchTerm = trim($searchTerm);

        if (empty($searchTerm)) {
            return new FriendsSearchDTO(new Collection(), null);
        }

        $users = $this->searchUsersWithFriendStatus($searchTerm, $currentUser->id, $currentUser);

        $friendStatuses = $this->buildFriendStatusesMap($users);

        return new FriendsSearchDTO($users, $searchTerm, $friendStatuses);
    }

    /**
     * Get friendship status between users.
     */
    private function getFriendStatus(User $currentUser, int $otherUserId): string
    {
        $request = $this->findExistingRequest($currentUser->id, $otherUserId);

        if (!$request) {
            return 'none';
        }

        if ($request->status === FriendRequestStatus::ACCEPTED->value) {
            return 'friends';
        }

        if ($request->status === FriendRequestStatus::PENDING->value) {
            return $request->sender_id === $currentUser->id ? 'request_sent' : 'request_received';
        }

        return 'none';
    }

    /**
     * Check if request is to self.
     */
    private function isSelfRequest(int $senderId, int $receiverId): bool
    {
        return $senderId === $receiverId;
    }

    /**
     * Find existing friend request between users.
     */
    private function findExistingRequest(int $senderId, int $receiverId): ?FriendRequest
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
     * Handle existing friend request.
     */
    private function handleExistingRequest(FriendRequest $request, User $sender, int $receiverId): bool|string
    {
        if ($request->status === FriendRequestStatus::ACCEPTED->value) {
            return __('messages.already_friends');
        }

        if ($request->status === FriendRequestStatus::PENDING->value) {
            return $request->sender_id === $sender->id
                ? __('messages.request_already_sent')
                : __('messages.request_already_received');
        }

        if ($request->status === FriendRequestStatus::DECLINED->value) {
            return $this->handleDeclinedRequest($request, $sender, $receiverId);
        }

        return true;
    }

    /**
     * Handle declined request.
     */
    private function handleDeclinedRequest(FriendRequest $request, User $sender, int $receiverId): bool
    {
        DB::transaction(function () use ($request, $sender, $receiverId) {
            if ($request->sender_id === $sender->id) {
                // If sender is trying to send request again, just delete the declined request
                $request->delete();
            } else {
                // If receiver is trying to send request, delete the declined request
                $request->delete();
            }
        });

        return true;
    }

    /**
     * Create new friend request.
     */
    private function createNewRequest(int $senderId, int $receiverId): bool
    {
        FriendRequest::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => FriendRequestStatus::PENDING->value,
        ]);

        $this->clearUserCaches($senderId, $receiverId);

        return true;
    }

    /**
     * Check if user can accept request.
     */
    private function canAcceptRequest(FriendRequest $request, int $receiverId): bool
    {
        return $request->receiver_id === $receiverId;
    }

    /**
     * Check if user can decline request.
     */
    private function canDeclineRequest(FriendRequest $request, int $receiverId): bool
    {
        return $request->receiver_id === $receiverId;
    }

    /**
     * Accept friend request.
     */
    private function acceptRequest(FriendRequest $request): void
    {
        $request->update(['status' => FriendRequestStatus::ACCEPTED->value]);

        $reverseRequest = $this->findExistingRequest($request->receiver_id, $request->sender_id);

        if ($reverseRequest) {
            $reverseRequest->update(['status' => FriendRequestStatus::ACCEPTED->value]);
        } else {
            $this->createNewRequest($request->receiver_id, $request->sender_id);
        }

        // Create friendship record in friends table
        $this->createFriendship($request->sender_id, $request->receiver_id);
    }

    /**
     * Delete friend requests between users.
     */
    private function deleteFriendRequests(int $userId, int $friendId): void
    {
        FriendRequest::whereRaw('(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)', [
            $userId, $friendId, $friendId, $userId
        ])->delete();
    }

    /**
     * Get friend IDs for a user.
     */
    private function getFriendIds(int $userId): \Illuminate\Support\Collection
    {
        return DB::table('friends')
            ->where('status', FriendRequestStatus::ACCEPTED->value)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('friend_id', $userId);
            })
            ->get()
            ->map(function ($friendship) use ($userId) {
                return $friendship->user_id == $userId ? $friendship->friend_id : $friendship->user_id;
            })
            ->unique()
            ->values();
    }

    /**
     * Build friend statuses map from users collection.
     */
    private function buildFriendStatusesMap(Collection $users): array
    {
        $friendStatuses = [];
        foreach ($users as $user) {
            $friendStatuses[$user->id] = $user->friend_status;
        }
        return $friendStatuses;
    }

    /**
     * Create friendship record in friends table.
     */
    private function createFriendship(int $userId, int $friendId): void
    {
        DB::table('friends')->insertOrIgnore([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => FriendRequestStatus::ACCEPTED->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Delete friendship record from friends table.
     */
    private function deleteFriendship(int $userId, int $friendId): void
    {
        DB::table('friends')->whereRaw('(user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)', [
            $userId, $friendId, $friendId, $userId
        ])->delete();
    }

    /**
     * Clear cache for multiple users.
     */
    private function clearUserCaches(int $firstUserId, int $secondUserId): void
    {
        $this->cacheService->clearUserCache($firstUserId);
        $this->cacheService->clearUserCache($secondUserId);
    }
}
