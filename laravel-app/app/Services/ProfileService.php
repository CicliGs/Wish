<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Services\WishListService;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use App\Services\AchievementsReceiver;
use App\DTOs\ProfileDTO;
use App\DTOs\FriendsSearchDTO;

class ProfileService
{
    private const AVATAR_STORAGE_PATH = 'avatars';
    private const MAX_AVATAR_SIZE = 2048;
    private const DEFAULT_SEARCH_LIMIT = 10;

    public function __construct(
        private readonly WishListService $wishListService,
        private readonly ReservationService $reservationService,
        private readonly AchievementsReceiver $achievementsReceiver
    ) {}

    /**
     * Get user statistics.
     */
    public function getUserStatistics(int $userId): array
    {
        $stats = $this->wishListService->getStatistics($userId);
        $reservationStats = $this->reservationService->getUserReservationStatistics($userId);

        return [
            'stats' => $stats,
            'reservationStats' => $reservationStats,
        ];
    }

    /**
     * Update user avatar with validation.
     */
    public function updateAvatar(User $user, UploadedFile $avatarFile): void
    {
        $this->validateAvatar($avatarFile);
        
        $path = $avatarFile->store(self::AVATAR_STORAGE_PATH, 'public');
        $user->avatar = '/storage/' . $path;
        $user->save();
    }

    /**
     * Search users (by name or email), excluding current user.
     */
    public function searchUsers(string $query, int $excludeUserId, int $limit = self::DEFAULT_SEARCH_LIMIT): Collection
    {
        return User::where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->where('id', '!=', $excludeUserId)
            ->limit($limit)
            ->get();
    }

    /**
     * Add friend (if not already friends and not referring to self).
     */
    public function addFriend(User $user, int $friendId): void
    {
        if ($this->canAddFriend($user, $friendId)) {
            $user->friends()->attach($friendId);
        }
    }

    /**
     * Remove friend.
     */
    public function removeFriend(User $user, int $friendId): void
    {
        $user->friends()->detach($friendId);
    }

    /**
     * Get user achievements.
     */
    public function getAchievements(User $user): array
    {
        $achievementsConfig = config('achievements');
        $result = [];

        foreach ($achievementsConfig as $achievement) {
            $achievementKey = $achievement['key'];
            $checker = $achievement['checker'] ?? null;
            $received = $user->hasAchievement($achievementKey);

            if (!$received && $checker && method_exists($this->achievementsReceiver, $checker)) {
                $received = $this->achievementsReceiver->{$checker}($user);
                if ($received) {
                    $this->createUserAchievement($user, $achievementKey);
                }
            }

            $result[] = [
                'key' => $achievementKey,
                'title' => __('messages.achievement_' . $achievementKey),
                'icon' => $achievement['icon'],
                'received' => $received,
            ];
        }

        return $result;
    }

    /**
     * Get profile data.
     */
    public function getProfileData(User $user, FriendService $friendService): ProfileDTO
    {
        $stats = $this->getUserStatistics($user->id);
        $achievements = $this->getAchievements($user);
        $friends = $friendService->getFriends($user);
        $incomingRequests = $friendService->getIncomingRequests($user);
        $outgoingRequests = $friendService->getOutgoingRequests($user);

        return new ProfileDTO(
            user: $user,
            stats: $stats,
            achievements: $achievements,
            friends: $friends,
            incomingRequests: $incomingRequests,
            outgoingRequests: $outgoingRequests
        );
    }

    /**
     * Update user name.
     */
    public function updateUserName(User $user, string $name): void
    {
        $this->validateUserName($name);
        $user->update(['name' => $name]);
    }

    /**
     * Search friends with status.
     */
    public function searchFriendsWithStatus(string $query, User $currentUser): FriendsSearchDTO
    {
        $users = app(FriendService::class)
            ->searchUsersWithFriendStatus($query, $currentUser->id, $currentUser);
        
        $friendStatuses = $this->buildFriendStatuses($users);
            
        return new FriendsSearchDTO(
            users: $users,
            query: $query,
            friendStatuses: $friendStatuses
        );
    }

    /**
     * Validate avatar file.
     */
    private function validateAvatar(UploadedFile $avatarFile): void
    {
        Validator::make(
            ['avatar' => $avatarFile],
            ['avatar' => 'required|image|max:' . self::MAX_AVATAR_SIZE]
        )->validate();
    }

    /**
     * Check if user can add friend.
     */
    private function canAddFriend(User $user, int $friendId): bool
    {
        return $user->id !== $friendId && !$user->friends()->where('friend_id', $friendId)->exists();
    }

    /**
     * Create user achievement.
     */
    private function createUserAchievement(User $user, string $achievementKey): void
    {
        $user->achievements()->create([
            'achievement_key' => $achievementKey,
            'received_at' => now(),
        ]);
    }

    /**
     * Validate user name.
     */
    private function validateUserName(string $name): void
    {
        Validator::make(
            ['name' => $name],
            ['name' => 'required|string|max:255']
        )->validate();
    }

    /**
     * Build friend statuses array.
     */
    private function buildFriendStatuses(Collection $users): array
    {
        $friendStatuses = [];
        foreach ($users as $user) {
            $friendStatuses[$user->id] = $user->friend_status ?? 'none';
        }
        return $friendStatuses;
    }
}
