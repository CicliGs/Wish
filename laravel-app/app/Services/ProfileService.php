<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use App\DTOs\ProfileDTO;

class ProfileService
{
    private const AVATAR_STORAGE_PATH = 'avatars';

    public function __construct(
        private readonly WishListService $wishListService,
        private readonly ReservationService $reservationService,
        private readonly AchievementsReceiver $achievementsReceiver,
        private readonly CacheManagerService $cacheManager
    ) {}

    public function getUserStatistics(int $userId): array
    {
        return array_merge(
            $this->wishListService->getStatistics($userId),
            $this->reservationService->getUserReservationStatistics($userId)
        );
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(User $user, UploadedFile $avatarFile): void
    {
        $path = $avatarFile->store(self::AVATAR_STORAGE_PATH, 'public');
        $user->update(['avatar' => '/storage/' . $path]);

        $this->cacheManager->clearUserCache($user->id);
    }

    /**
     * Update user name
     */
    public function updateUserName(User $user, string $name): void
    {
        $user->update(['name' => $name]);
        $this->cacheManager->clearUserCache($user->id);
    }

    /**
     * Get user achievements
     */
    public function getAchievements(User $user): array
    {
        return collect(config('achievements'))->map(function ($achievement) use ($user) {
            $achievementKey = $achievement['key'];
            $received = $user->hasAchievement($achievementKey);

            if (!$received && !($achievement['auto_grant'] ?? false)) {
                $checker = $achievement['checker'] ?? null;
                if ($checker && method_exists($this->achievementsReceiver, $checker)) {
                    $received = $this->achievementsReceiver->{$checker}($user);
                    if ($received) {
                        $this->createUserAchievement($user, $achievementKey);
                    }
                }
            }

            return [
                'key' => $achievementKey,
                'title' => __('messages.achievement_' . $achievementKey),
                'icon' => $achievement['icon'],
                'received' => $received,
            ];
        })->toArray();
    }

    /**
     * Get profile data with caching
     */
    public function getProfileData(User $user, FriendService $friendService): ProfileDTO
    {
        $cacheKey = "user_profile_$user->id";
        $cachedData = $this->cacheManager->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        $dto = ProfileDTO::fromUserWithData(
            user: $user,
            stats: $this->getUserStatistics($user->id),
            friends: $friendService->getFriends($user),
            incomingRequests: $friendService->getIncomingRequests($user),
            outgoingRequests: $friendService->getOutgoingRequests($user),
            achievements: $this->getAchievements($user),
            wishLists: $this->wishListService->findByUser($user->id)
        );

        $this->cacheManager->cacheService->cacheStaticContent($cacheKey, serialize($dto), 900);
        return $dto;
    }

    /**
     * Create user achievement
     */
    private function createUserAchievement(User $user, string $achievementKey): void
    {
        $user->achievements()->create([
            'achievement_key' => $achievementKey,
            'received_at' => now(),
        ]);
    }
}
