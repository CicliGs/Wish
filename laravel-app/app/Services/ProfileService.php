<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use App\DTOs\ProfileDTO;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    private const AVATAR_STORAGE_PATH = 'avatars';
    private const MAX_AVATAR_SIZE = 2048;
    private const DEFAULT_SEARCH_LIMIT = 10;

    public function __construct(
        private readonly WishListService $wishListService,
        private readonly ReservationService $reservationService,
        private readonly AchievementsReceiver $achievementsReceiver,
        private readonly CacheService $cacheService
    ) {}

    public function getUserStatistics(int $userId): array
    {
        return array_merge(
            $this->wishListService->getStatistics($userId),
            $this->reservationService->getUserReservationStatistics($userId)
        );
    }

    /**
     * @throws ValidationException
     */
    public function updateAvatar(User $user, UploadedFile $avatarFile): void
    {
        $this->validateAvatar($avatarFile);

        $path = $avatarFile->store(self::AVATAR_STORAGE_PATH, 'public');
        $user->update(['avatar' => '/storage/' . $path]);

        $this->cacheService->clearUserCache($user->id);
    }

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
     * @throws ValidationException
     */
    public function updateUserName(User $user, string $name): void
    {
        $this->validateUserName($name);
        $user->update(['name' => $name]);
        $this->cacheService->clearUserCache($user->id);
    }

    public function getProfileData(User $user, FriendService $friendService): ProfileDTO
    {
        $cacheKey = "user_profile_$user->id";
        $cachedData = $this->cacheService->getStaticContent($cacheKey);

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

        $this->cacheService->cacheStaticContent($cacheKey, serialize($dto), 900);
        return $dto;
    }

    /**
     * @throws ValidationException
     */
    private function validateAvatar(UploadedFile $avatarFile): void
    {
        Validator::make(
            ['avatar' => $avatarFile],
            ['avatar' => 'required|image|max:' . self::MAX_AVATAR_SIZE]
        )->validate();
    }

    private function createUserAchievement(User $user, string $achievementKey): void
    {
        $user->achievements()->create([
            'achievement_key' => $achievementKey,
            'received_at' => now(),
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function validateUserName(string $name): void
    {
        Validator::make(
            ['name' => $name],
            ['name' => 'required|string|max:255']
        )->validate();
    }
}
