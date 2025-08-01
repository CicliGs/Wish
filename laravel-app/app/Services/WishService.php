<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class WishService
{
    public function findByWishList(int $wishListId): Collection
    {
        return Wish::forWishList($wishListId)->with('reservation.user')->get();
    }

    /**
     * Find a wish by ID and wish list ID.
     */
    public function findByIdAndWishList(int $wishId, int $wishListId): ?Wish
    {
        return Wish::forWishList($wishListId)->find($wishId);
    }

    public function create(array $data, int $wishListId): Wish
    {
        $this->validateCreateData($data);
        $data['wish_list_id'] = $wishListId;

        return Wish::create($data);
    }

    public function update(Wish $wish, array $data): Wish
    {
        $this->validateUpdateData($data);
        $wish->update($data);

        return $wish->fresh();
    }

    public function delete(Wish $wish): bool
    {
        return $wish->delete();
    }

    /**
     * Reserve a wish for a user.
     */
    public function reserveWish(Wish $wish, int $userId): bool
    {
        if (! $wish->isAvailable()) {
            return false;
        }

        return $wish->reserveForUser($userId);
    }

    /**
     * Unreserve a wish.
     */
    public function unreserveWish(Wish $wish, int $userId): bool
    {
        if (! $wish->hasReservation() || $wish->getReservedByUser()->id !== $userId) {
            return false;
        }

        return $wish->dereserve();
    }

    /**
     * Get available wishes for a wish list.
     */
    public function getAvailableWishes(int $wishListId): Collection
    {
        return Wish::forWishList($wishListId)->available()->get();
    }

    /**
     * Get reserved wishes for a wish list.
     */
    public function getReservedWishes(int $wishListId): Collection
    {
        return Wish::forWishList($wishListId)->reserved()->with('reservation.user')->get();
    }

    public function getAllUserWishesWithLists(int $userId)
    {
        return \App\Models\Wish::whereHas('wishList', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('wishList')->get();
    }

    public function getWishListStatistics(int $wishListId): array
    {
        $wishes = Wish::forWishList($wishListId);

        return [
            'total_wishes' => $wishes->count(),
            'available_wishes' => $wishes->available()->count(),
            'reserved_wishes' => $wishes->reserved()->count(),
            'total_value' => $wishes->sum('price'),
        ];
    }

    /**
     * Получает данные для страницы списков пользователя.
     */
    public function getUserWishListsData(int $userId): array
    {
        $user = User::findOrFail($userId);
        $wishLists = WishList::where('user_id', $userId)->get();
        
        return [
            'user' => $user,
            'wishLists' => $wishLists
        ];
    }

    /**
     * Получает данные для страницы конкретного списка пользователя.
     */
    public function getUserWishListData(int $userId, int $wishListId): array
    {
        $user = User::findOrFail($userId);
        $wishList = WishList::where('id', $wishListId)
            ->where('user_id', $userId)
            ->firstOrFail();
        $wishes = $wishList->wishes;

        // Логика для модальных окон
        $isGuest = !auth()->check();
        $isFriend = false;
        if (auth()->check()) {
            $currentUser = auth()->user();
            $isFriend = app(\App\Services\FriendService::class)->isAlreadyFriendOrRequested($currentUser, $user->id);
        }

        return [
            'user' => $user,
            'wishList' => $wishList,
            'wishes' => $wishes,
            'isGuest' => $isGuest,
            'isFriend' => $isFriend
        ];
    }

    /**
     * Проверяет возможность отмены бронирования.
     */
    public function canUnreserveWish(Wish $wish, int $userId): bool
    {
        return $wish->is_reserved && 
               $wish->reservation && 
               $wish->reservation->user_id === $userId;
    }

    /**
     * Проверяет возможность бронирования подарка.
     */
    public function canReserveWish(Wish $wish): bool
    {
        return auth()->check() && $wish->isAvailable();
    }

    /**
     * Обрабатывает загрузку файла изображения.
     */
    public function handleImageUpload($file): string
    {
        $path = $file->store('wishes', 'public');
        return '/storage/' . $path;
    }

    /**
     * Создает желание с обработкой файла.
     */
    public function createWithImage(array $data, int $wishListId, $imageFile = null): void
    {
        if ($imageFile) {
            $data['image'] = $this->handleImageUpload($imageFile);
        }
        
        $this->create($data, $wishListId);
    }

    private function validateCreateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'string', 'max:500'], // изменено с 'url' на 'string'
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function validateUpdateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'string', 'max:500'], // изменено с 'url' на 'string'
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Получает данные для страницы списка желаний.
     */
    public function getIndexData(int $wishListId, int $userId): array
    {
        $wishList = WishList::forUser($userId)->findOrFail($wishListId);
        $wishes = $this->findByWishList($wishList->id);
        $statistics = $this->getWishListStatistics($wishList->id);

        return [
            'wishes' => $wishes,
            'wishList' => $wishList,
            'statistics' => $statistics
        ];
    }

    /**
     * Получает данные для страницы доступных желаний.
     */
    public function getAvailableData(int $wishListId, int $userId): array
    {
        $wishList = WishList::forUser($userId)->findOrFail($wishListId);
        $wishes = $this->getAvailableWishes($wishList->id);

        return [
            'wishes' => $wishes,
            'wishList' => $wishList
        ];
    }

    /**
     * Получает данные для страницы забронированных желаний.
     */
    public function getReservedData(int $wishListId, int $userId): array
    {
        $wishList = WishList::forUser($userId)->findOrFail($wishListId);
        $wishes = $this->getReservedWishes($wishList->id);

        return [
            'wishes' => $wishes,
            'wishList' => $wishList
        ];
    }
}
