<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\FriendsDTO;
use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FriendService
{
    //TODO enum
    private const STATUS_ACCEPTED = 'accepted';
    private const STATUS_PENDING = 'pending';
    private const STATUS_DECLINED = 'declined';

    /**
     * Send friend request by user ID.
     */
    public function sendRequestToUserId(User $from, int $userId): bool|string
    {
        if ($this->isSelfRequest($from->id, $userId)) {
            return __('messages.cannot_add_self_as_friend');
        }

        $existingRequest = $this->findExistingRequest($from->id, $userId);

        if ($existingRequest) {
            return $this->handleExistingRequest($existingRequest, $from, $userId);
        }

        return $this->createNewRequest($from->id, $userId);
    }

    /**
     * Accept request by ID.
     */
    public function acceptRequestById(int $requestId, int $authUserId): void
    {
        $request = FriendRequest::findOrFail($requestId);

        if (!$this->canAcceptRequest($request, $authUserId)) {
            throw new HttpException(403, __('messages.access_denied'));
        }

        DB::transaction(function () use ($request) {
            $this->acceptRequest($request);
        });
    }

    /**
     * Decline request by ID.
     */
    public function declineRequestById(int $requestId, int $authUserId): void
    {
        $request = FriendRequest::findOrFail($requestId);

        if (!$this->canDeclineRequest($request, $authUserId)) {
            throw new HttpException(403, __('messages.access_denied'));
        }

        $request->update(['status' => self::STATUS_DECLINED]);
    }

    /**
     * Remove friend by ID.
     */
    public function removeFriendById(User $user, int $friendId): void
    {
        DB::transaction(function () use ($user, $friendId) {
            $this->deleteFriendRequests($user->id, $friendId);
        });
    }

    /**
     * Get friends for a user.
     */
    public function getFriends(User $user): Collection
    {
        return FriendRequest::where('status', self::STATUS_ACCEPTED)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->with(['sender', 'receiver'])
            ->get()
            ->map(function ($request) use ($user) {
                return $this->getFriendFromRequest($request, $user);
            })
            ->unique('id');
    }

    /**
     * Get incoming friend requests.
     */
    public function getIncomingRequests(User $user): Collection
    {
        return FriendRequest::where('receiver_id', $user->id)
            ->where('status', self::STATUS_PENDING)
            ->with('sender')
            ->get();
    }

    /**
     * Get outgoing friend requests.
     */
    public function getOutgoingRequests(User $user): Collection
    {
        return FriendRequest::where('user_id', $user->id)
            ->where('status', self::STATUS_PENDING)
            ->with('receiver')
            ->get();
    }

    /**
     * Check if users are already friends or have pending requests.
     */
    public function isAlreadyFriendOrRequested(User $from, int $toId): bool
    {
        return FriendRequest::where(function ($query) use ($from, $toId) {
            $query->where('user_id', $from->id)
                  ->where('receiver_id', $toId);
        })->orWhere(function ($query) use ($from, $toId) {
            $query->where('user_id', $toId)
                  ->where('receiver_id', $from->id);
        })->exists();
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
     * Search users with friend status.
     */
    public function searchUsersWithFriendStatus(string $query, int $excludeUserId, User $currentUser): Collection
    {
        return User::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->where('id', '!=', $excludeUserId)
            ->limit(10)
            ->get()
            ->map(function ($user) use ($currentUser) {
                $user->friend_status = $this->getFriendStatus($currentUser, $user->id);
                return $user;
            });
    }

    /**
     * Get friend status between users.
     */
    private function getFriendStatus(User $from, int $toId): string
    {
        $request = $this->findExistingRequest($from->id, $toId);

        if (!$request) {
            return 'none';
        }

        if ($request->status === self::STATUS_ACCEPTED) {
            return 'friends';
        }

        if ($request->status === self::STATUS_PENDING) {
            return $request->user_id === $from->id ? 'request_sent' : 'request_received';
        }

        return 'none';
    }

    /**
     * Check if request is to self.
     */
    private function isSelfRequest(int $fromId, int $toId): bool
    {
        return $fromId === $toId;
    }

    /**
     * Find existing friend request.
     */
    private function findExistingRequest(int $fromId, int $toId): ?FriendRequest
    {
        return FriendRequest::where(function ($query) use ($fromId, $toId) {
            $query->where('user_id', $fromId)
                  ->where('receiver_id', $toId);
        })->orWhere(function ($query) use ($fromId, $toId) {
            $query->where('user_id', $toId)
                  ->where('receiver_id', $fromId);
        })->first();
    }

    /**
     * Handle existing request.
     */
    private function handleExistingRequest(FriendRequest $request, User $from, int $userId): bool|string
    {
        if ($request->status === self::STATUS_ACCEPTED) {
            return __('messages.already_friends');
        }

        if ($request->status === self::STATUS_PENDING) {
            return $request->user_id === $from->id
                ? __('messages.request_already_sent')
                : __('messages.request_already_received');
        }

        if ($request->status === self::STATUS_DECLINED) {
            return $this->handleDeclinedRequest($request, $from, $userId);
        }

        return true;
    }

    /**
     * Handle declined request.
     */
    private function handleDeclinedRequest(FriendRequest $request, User $from, int $userId): bool
    {
        DB::transaction(function () use ($request, $from, $userId) {
            if ($request->user_id === $from->id) {
                $request->update(['status' => self::STATUS_PENDING]);
            } else {
                $request->delete();
                $this->createNewRequest($from->id, $userId);
            }
        });

        return true;
    }

    /**
     * Create new friend request.
     */
    private function createNewRequest(int $fromId, int $toId): bool
    {
        FriendRequest::create([
            'user_id' => $fromId,
            'receiver_id' => $toId,
            'status' => self::STATUS_PENDING,
        ]);

        return true;
    }

    /**
     * Check if user can accept request.
     */
    private function canAcceptRequest(FriendRequest $request, int $authUserId): bool
    {
        return $request->receiver_id === $authUserId;
    }

    /**
     * Check if user can decline request.
     */
    private function canDeclineRequest(FriendRequest $request, int $authUserId): bool
    {
        return $request->receiver_id === $authUserId;
    }

    /**
     * Accept friend request.
     */
    private function acceptRequest(FriendRequest $request): void
    {
        $request->update(['status' => self::STATUS_ACCEPTED]);

        $reverseRequest = $this->findExistingRequest($request->receiver_id, $request->user_id);

        if ($reverseRequest) {
            $reverseRequest->update(['status' => self::STATUS_ACCEPTED]);
        } else {
            $this->createNewRequest($request->receiver_id, $request->user_id);
        }
    }

    /**
     * Delete friend requests.
     */
    private function deleteFriendRequests(int $userId, int $friendId): void
    {
        FriendRequest::where(function ($query) use ($userId, $friendId) {
            $query->where('user_id', $userId)
                  ->where('receiver_id', $friendId);
        })->orWhere(function ($query) use ($userId, $friendId) {
            $query->where('user_id', $friendId)
                  ->where('receiver_id', $userId);
        })->delete();
    }

    /**
     * Get friend from request.
     */
    private function getFriendFromRequest(FriendRequest $request, User $user): User
    {
        return $request->user_id === $user->id ? $request->receiver : $request->sender;
    }
}
