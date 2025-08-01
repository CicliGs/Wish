<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Services\WishListService;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use App\Services\AchievementCheckers;

class ProfileService
{
    private WishListService $wishListService;
    private ReservationService $reservationService;
    private AchievementCheckers $achievementCheckers;

    public function __construct(
        WishListService $wishListService, 
        ReservationService $reservationService, 
        AchievementCheckers $achievementCheckers
    ) {
        $this->wishListService = $wishListService;
        $this->reservationService = $reservationService;
        $this->achievementCheckers = $achievementCheckers;
    }

    /**
     * Получение статистики пользователя.
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
     * Обновление аватара пользователя с валидацией.
     */
    public function updateAvatar(User $user, UploadedFile $avatarFile): void
    {
        $validated = Validator::make(
            ['avatar' => $avatarFile],
            ['avatar' => 'required|image|max:2048']
        )->validate();

        $path = $avatarFile->store('avatars', 'public');
        $user->avatar = '/storage/' . $path;
        $user->save();
    }

    /**
     * Поиск пользователей (по имени или email), исключая текущего.
     */
    public function searchUsers(string $query, int $excludeUserId, int $limit = 10): Collection
    {
        return User::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->where('id', '!=', $excludeUserId)
            ->limit($limit)
            ->get();
    }

    /**
     * Добавление друга (если ещё нет в друзьях и не ссылается на самого себя).
     */
    public function addFriend(User $user, int $friendId): void
    {
        if ($user->id !== $friendId && !$user->friends()->where('friend_id', $friendId)->exists()) {
            $user->friends()->attach($friendId);
        }
    }

    /**
     * Удаление друга.
     */
    public function removeFriend(User $user, int $friendId): void
    {
        $user->friends()->detach($friendId);
    }

    /**
     * Получение достижений пользователя.
     */
    public function getAchievements(User $user): array
    {
        $achievementsConfig = config('achievements');
        $result = [];
        
        foreach ($achievementsConfig as $achievement) {
            $checker = $achievement['checker'] ?? null;
            $received = false;
            
            if ($checker && method_exists($this->achievementCheckers, $checker)) {
                $received = $this->achievementCheckers->{$checker}($user);
            }
            
            $result[] = [
                'key' => $achievement['key'],
                'title' => __('messages.achievement_' . $achievement['key']),
                'icon' => $achievement['icon'],
                'received' => $received,
            ];
        }
        
        return $result;
    }

    /**
     * Собирает все данные для профиля пользователя для передачи в view.
     */
    public function getProfileData(User $user, FriendService $friendService): array
    {
        $stats = $this->getUserStatistics($user->id);
        $friends = $friendService->getFriends($user);
        $incoming = $friendService->getIncomingRequests($user);
        $outgoing = $friendService->getOutgoingRequests($user);
        $achievements = $this->getAchievements($user);
        
        // Объединяем статистики
        $combinedStats = array_merge($stats['stats'], $stats['reservationStats']);
        
        return [
            'stats' => $combinedStats,
            'friends' => $friends,
            'incomingRequests' => $incoming,
            'outgoingRequests' => $outgoing,
            'achievements' => $achievements,
        ];
    }

    /**
     * Обновление имени пользователя.
     */
    public function updateUserName(User $user, string $name): void
    {
        $user->name = $name;
        $user->save();
    }

    /**
     * Поиск пользователей для добавления в друзья (с пометкой, если уже друг или заявка отправлена).
     */
    public function searchFriendsWithStatus(string $query, User $currentUser): Collection
    {
        return app(FriendService::class)
            ->searchUsersWithFriendStatus($query, $currentUser->id, $currentUser);
    }
}
