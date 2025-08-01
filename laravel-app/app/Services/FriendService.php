<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FriendService
{
    /**
     * Отправка заявки в друзья.
     */
    public function sendRequest(User $from, User $to): bool
    {
        if ($from->id === $to->id) {
            throw new \InvalidArgumentException(__('messages.cannot_add_self_as_friend'));
        }

        $exists = FriendRequest::where(function ($query) use ($from, $to) {
            $query->where('user_id', $from->id)
                  ->where('friend_id', $to->id);
        })->orWhere(function ($query) use ($from, $to) {
            $query->where('user_id', $to->id)
                  ->where('friend_id', $from->id);
        })->whereIn('status', ['pending', 'accepted'])->exists();

        if ($exists) {
            throw new \InvalidArgumentException(__('messages.request_already_sent_or_friends'));
        }

        FriendRequest::create([
            'user_id' => $from->id,
            'friend_id' => $to->id,
            'status' => 'pending',
        ]);

        return true;
    }

    /**
     * Обёртка по поиску пользователей и отправке заявки.
     */
    public function processSendRequest(int $fromId, int $toId): bool
    {
        $from = User::findOrFail($fromId);
        $to = User::findOrFail($toId);

        return $this->sendRequest($from, $to);
    }

    /**
     * Принятие запроса на добавление друзей.
     */
    public function acceptRequest(FriendRequest $request, int $authUserId): void
    {
        if ($request->friend_id !== $authUserId) {
            throw new HttpException(403, __('messages.access_denied'));
        }

        // Обёртываем в транзакцию, чтобы избежать частичных изменений
        DB::transaction(function () use ($request) {
            $request->update(['status' => 'accepted']);
            // Создаём обратную запись, если её нет
            FriendRequest::updateOrCreate([
                'user_id' => $request->friend_id,
                'friend_id' => $request->user_id,
            ], [
                'status' => 'accepted',
            ]);
        });
    }

    /**
     * Обёртка для обработки принятия заявки по ID.
     */
    public function processAcceptRequest(int $requestId, int $authUserId): void
    {
        $request = FriendRequest::findOrFail($requestId);
        $this->acceptRequest($request, $authUserId);
    }

    /**
     * Отклонение заявки.
     */
    public function declineRequest(FriendRequest $request, int $authUserId): void
    {
        if ($request->friend_id !== $authUserId) {
            throw new HttpException(403, __('messages.access_denied'));
        }

        $request->update(['status' => 'declined']);
    }

    /**
     * Обёртка для обработки отклонения заявки по ID.
     */
    public function processDeclineRequest(int $requestId, int $authUserId): void
    {
        $request = FriendRequest::findOrFail($requestId);
        $this->declineRequest($request, $authUserId);
    }

    /**
     * Удаление друга.
     */
    public function removeFriend(User $user, User $friend): void
    {
        // Удаляем обе записи о дружбе
        FriendRequest::where(function ($query) use ($user, $friend) {
            $query->where('user_id', $user->id)
                  ->where('friend_id', $friend->id);
        })->orWhere(function ($query) use ($user, $friend) {
            $query->where('user_id', $friend->id)
                  ->where('friend_id', $user->id);
        })->where('status', 'accepted')->delete();
    }

    /**
     * Обёртка для удаления друга по ID.
     */
    public function processRemoveFriend(int $userId, int $friendId): void
    {
        $user = User::findOrFail($userId);
        $friend = User::findOrFail($friendId);

        $this->removeFriend($user, $friend);
    }

    /**
     * Получение списка друзей пользователя.
     */
    public function getFriends(User $user): Collection
    {
        return User::whereHas('sentRequests', function ($query) use ($user) {
            $query->where('friend_id', $user->id)
                  ->where('status', 'accepted');
        })->orWhereHas('receivedRequests', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'accepted');
        })->get();
    }

    /**
     * Получение входящих заявок в друзья.
     */
    public function getIncomingRequests(User $user): Collection
    {
        return FriendRequest::with('sender')
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->get();
    }

    /**
     * Получение исходящих заявок в друзья.
     */
    public function getOutgoingRequests(User $user): Collection
    {
        return FriendRequest::with('receiver')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();
    }

    /**
     * Поиск пользователей по имени или email.
     */
    public function searchUsers(string $query, int $excludeId): Collection
    {
        return User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })
        ->where('id', '!=', $excludeId)
        ->limit(10)
        ->get();
    }

    /**
     * Проверка, является ли пользователь другом или уже отправлена заявка.
     */
    public function isAlreadyFriendOrRequested(User $from, int $toId): bool
    {
        return FriendRequest::where(function ($query) use ($from, $toId) {
            $query->where('user_id', $from->id)
                  ->where('friend_id', $toId);
        })->orWhere(function ($query) use ($from, $toId) {
            $query->where('user_id', $toId)
                  ->where('friend_id', $from->id);
        })->whereIn('status', ['pending', 'accepted'])->exists();
    }

    /**
     * Отправка заявки в друзья по ID пользователя.
     */
    public function sendRequestToUserId(User $from, int $userId): bool|string
    {
        if ($from->id === $userId) {
            return __('messages.cannot_add_self_as_friend');
        }

        // Проверяем существующую заявку
        $existingRequest = FriendRequest::where(function ($query) use ($from, $userId) {
            $query->where('user_id', $from->id)
                  ->where('friend_id', $userId);
        })->orWhere(function ($query) use ($from, $userId) {
            $query->where('user_id', $userId)
                  ->where('friend_id', $from->id);
        })->first();

        if ($existingRequest) {
            if ($existingRequest->status === 'accepted') {
                return __('messages.already_friends');
            }
            
            if ($existingRequest->status === 'pending') {
                if ($existingRequest->user_id === $from->id) {
                    return __('messages.request_already_sent');
                } else {
                    return __('messages.request_already_received');
                }
            }
            
            if ($existingRequest->status === 'declined') {
                // Если заявка была отклонена, обновляем её
                DB::transaction(function () use ($existingRequest, $from, $userId) {
                    if ($existingRequest->user_id === $from->id) {
                        // Заявка была отправлена этим пользователем, обновляем её
                        $existingRequest->update(['status' => 'pending']);
                    } else {
                        // Заявка была отправлена другим пользователем, удаляем старую и создаём новую
                        $existingRequest->delete();
                        FriendRequest::create([
                            'user_id' => $from->id,
                            'friend_id' => $userId,
                            'status' => 'pending',
                        ]);
                    }
                });
                return true;
            }
        }

        // Создаём новую заявку
        FriendRequest::create([
            'user_id' => $from->id,
            'friend_id' => $userId,
            'status' => 'pending',
        ]);

        return true;
    }

    /**
     * Принятие заявки по ID.
     */
    public function acceptRequestById(int $requestId, int $authUserId): void
    {
        $request = FriendRequest::findOrFail($requestId);
        $this->acceptRequest($request, $authUserId);
    }

    /**
     * Отклонение заявки по ID.
     */
    public function declineRequestById(int $requestId, int $authUserId): void
    {
        $request = FriendRequest::findOrFail($requestId);
        $this->declineRequest($request, $authUserId);
    }

    /**
     * Удаление друга по ID.
     */
    public function removeFriendById(User $user, int $friendId): void
    {
        $friend = User::findOrFail($friendId);
        $this->removeFriend($user, $friend);
    }

    /**
     * Получение данных для страницы друзей.
     */
    public function getFriendsPageData(User $user, ?int $selectedFriendId = null): array
    {
        $friends = $this->getFriends($user);
        $incomingRequests = $this->getIncomingRequests($user);
        $outgoingRequests = $this->getOutgoingRequests($user);
        
        $selectedFriend = null;
        if ($selectedFriendId) {
            $selectedFriend = $friends->firstWhere('id', $selectedFriendId);
        }

        return [
            'friends' => $friends,
            'incomingRequests' => $incomingRequests,
            'outgoingRequests' => $outgoingRequests,
            'selectedFriend' => $selectedFriend,
        ];
    }

    /**
     * Получение статуса дружбы между пользователями.
     */
    public function getFriendStatus(User $from, int $toId): string
    {
        $request = FriendRequest::where(function ($query) use ($from, $toId) {
            $query->where('user_id', $from->id)
                  ->where('friend_id', $toId);
        })->orWhere(function ($query) use ($from, $toId) {
            $query->where('user_id', $toId)
                  ->where('friend_id', $from->id);
        })->first();

        if (!$request) {
            return 'none';
        }

        return $request->status;
    }

    /**
     * Поиск пользователей с информацией о статусе дружбы.
     */
    public function searchUsersWithFriendStatus(string $query, int $excludeUserId, User $currentUser): Collection
    {
        $users = $this->searchUsers($query, $excludeUserId);
        
        return $users->map(function ($user) use ($currentUser) {
            $user->friend_status = $this->getFriendStatus($currentUser, $user->id);
            return $user;
        });
    }
}
