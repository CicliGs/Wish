<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\AchievementRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use App\DTOs\ProfileDTO;

class ProfileService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly WishListService $wishListService,
        private readonly ReservationService $reservationService,
        private readonly AchievementsReceiver $achievementsReceiver,
        private readonly CacheManagerService $cacheManager,
        private readonly UserRepositoryInterface $userRepository,
        private readonly AchievementRepositoryInterface $achievementRepository,
        private readonly ConfigRepository $config
    ) {}

    /**
     * Get user statistics.
     */
    public function getStatistics(User $user): array
    {
        return array_merge(
            $this->wishListService->getStatistics($user),
            $this->reservationService->getStatistics($user)
        );
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(User $user, string $avatarPath): void
    {
        $this->userRepository->update($user, ['avatar' => $avatarPath]);
        $this->cacheManager->clearUserCache($user->id);
    }

    /**
     * Update user name
     */
    public function updateUserName(User $user, string $name): void
    {
        $this->userRepository->update($user, ['name' => $name]);
        $this->cacheManager->clearUserCache($user->id);
    }

    /**
     * Get user achievements
     *
     * @return array<int, array<string, mixed>> Array of achievements with status
     */
    public function getAchievements(User $user): array
    {
        /** @var array<int, array<string, mixed>> $achievements */
        $achievements = $this->config->get('achievements', []);

        return collect($achievements)->map(function ($achievement) use ($user) {
            $achievementKey = $achievement['key'];
            $received = $this->achievementRepository->userHasAchievement($user, $achievementKey);

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
            stats: $this->getStatistics($user),
            friends: $friendService->getFriends($user)->all(),
            incomingRequests: $friendService->getIncomingRequests($user)->all(),
            outgoingRequests: $friendService->getOutgoingRequests($user)->all(),
            achievements: $this->getAchievements($user),
            wishLists: $this->wishListService->findWishLists($user)->all()
        );

        $this->cacheManager->cacheService->cacheStaticContent($cacheKey, serialize($dto), 900);

        return $dto;
    }

    /**
     * Create user achievement
     *
     * @param string $achievementKey The achievement key to create
     */
    private function createUserAchievement(User $user, string $achievementKey): void
    {
        $this->achievementRepository->createForUser($user, $achievementKey);
    }
}
