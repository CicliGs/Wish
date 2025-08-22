<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WishDTO;
use App\DTOs\UserWishesDTO;
use App\Http\Requests\StoreWishRequest;
use App\Http\Requests\UpdateWishRequest;
use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class WishService
{
    private const STORAGE_PATH = 'wishes';

    public function __construct(
        protected CacheService $cacheService
    ) {}

    /**
     * Find wishes by wish list ID.
     */
    public function findByWishList(int $wishListId): Collection
    {
        return Wish::forWishList($wishListId)->with('reservation.user')->get();
    }

    /**
     * Create a new wish.
     */
    public function create(StoreWishRequest $request, int $wishListId): Wish
    {
        $data = $request->getWishData();
        $data['wish_list_id'] = $wishListId;

        $wish = Wish::create($data);

        // Очищаем кеш пользователя после создания желания
        $this->clearUserCacheByWishList($wishListId);

        return $wish;
    }

    /**
     * Update an existing wish.
     */
    public function update(Wish $wish, UpdateWishRequest $request): Wish
    {
        $data = $request->getWishData();
        $wish->update($data);

        // Очищаем кеш пользователя после обновления желания
        $this->clearUserCacheByWishList($wish->wish_list_id);

        return $wish->fresh();
    }

    /**
     * Delete a wish.
     */
    public function delete(Wish $wish): bool
    {
        $wishListId = $wish->wish_list_id;
        $result = $wish->delete();

        // Очищаем кеш пользователя после удаления желания
        if ($result) {
            $this->clearUserCacheByWishList($wishListId);
        }

        return $result;
    }

    /**
     * Reserve a wish for a user.
     */
    public function reserveWish(Wish $wish, int $userId): bool
    {
        if (!$wish->isAvailable()) {
            return false;
        }

        return $wish->reserveForUser($userId);
    }

    /**
     * Unreserve a wish.
     */
    public function unreserveWish(Wish $wish, int $userId): bool
    {
        if (!$wish->hasReservation()) {
            return false;
        }

        $reservedByUser = $wish->getReservedByUser();
        if (!$reservedByUser || $reservedByUser->id !== $userId) {
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

    /**
     * Get all user wishes with lists.
     */
    public function getAllUserWishesWithLists(int $userId): Collection
    {
        return Wish::whereHas('wishList', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('wishList')->get();
    }

    /**
     * Get wish list statistics.
     */
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
     * Get data for user wish lists page.
     */
    public function getUserWishListsData(int $userId): UserWishesDTO
    {
        $user = User::findOrFail($userId);
        $wishLists = WishList::where('user_id', $userId)->get();
        $wishes = $this->getUserWishes($userId);

        return new UserWishesDTO(
            user: $user,
            wishLists: $wishLists,
            wishes: $wishes
        );
    }

    /**
     * Get data for specific user wish list page.
     */
    public function getUserWishListData(int $userId, int $wishListId): UserWishesDTO
    {
        $user = User::findOrFail($userId);
        $wishList = $this->findWishListByUser($wishListId, $userId);
        $wishes = $wishList->wishes()->with('reservation.user')->get();

        return new UserWishesDTO(
            user: $user,
            wishLists: collect([$wishList]),
            selectedWishList: $wishList,
            wishes: $wishes
        );
    }

    /**
     * Check if user can unreserve a wish.
     */
    public function canUnreserveWish(Wish $wish, int $userId): bool
    {
        return $wish->is_reserved &&
               $wish->reservation &&
               $wish->reservation->user_id === $userId;
    }

    /**
     * Check if user can reserve a wish.
     */
    public function canReserveWish(Wish $wish): bool
    {
        return auth()->check() && $wish->isAvailable();
    }

    /**
     * Handle image upload.
     */
    public function handleImageUpload(UploadedFile $file): string
    {
        $path = $file->store(self::STORAGE_PATH, 'public');
        return '/storage/' . $path;
    }

    /**
     * Create wish with image handling.
     */
    public function createWithImage(StoreWishRequest $request, int $wishListId, ?UploadedFile $imageFile = null): Wish
    {
        // Handle image upload if provided
        if ($imageFile) {
            $imagePath = $this->handleImageUpload($imageFile);
            // We need to modify the request data to include the image path
            $request->merge(['image' => $imagePath]);
        }

        return $this->create($request, $wishListId);
    }

    /**
     * Get index data for wish list with caching.
     */
    public function getIndexData(int $wishListId, int $userId): WishDTO
    {
        $cacheKey = "wishes_list_{$wishListId}_user_$userId";

        // Попытка получить данные из кеша
        $cachedData = $this->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        // Если кеша нет, получаем данные и кешируем их
        $wishList = WishList::findOrFail($wishListId);
        $wishes = $this->findByWishList($wishListId);
        $stats = $this->getWishListStatistics($wishListId);

        $dto = new WishDTO(
            wishList: $wishList,
            wishes: $wishes,
            stats: $stats,
            userId: $userId
        );

        // Кешируем данные на 30 минут (1800 секунд)
        $this->cacheService->cacheStaticContent($cacheKey, serialize($dto), 1800);

        return $dto;
    }

    /**
     * Get available data for wish list.
     */
    public function getAvailableData(int $wishListId, int $userId): WishDTO
    {
        $wishList = WishList::findOrFail($wishListId);
        $wishes = $this->getAvailableWishes($wishListId);
        $stats = $this->getWishListStatistics($wishListId);

        return new WishDTO(
            wishList: $wishList,
            wishes: $wishes,
            stats: $stats,
            userId: $userId
        );
    }

    /**
     * Get reserved data for wish list.
     */
    public function getReservedData(int $wishListId, int $userId): WishDTO
    {
        $wishList = WishList::findOrFail($wishListId);
        $wishes = $this->getReservedWishes($wishListId);
        $stats = $this->getWishListStatistics($wishListId);

        return new WishDTO(
            wishList: $wishList,
            wishes: $wishes,
            stats: $stats,
            userId: $userId
        );
    }



    /**
     * Get user wishes.
     */
    private function getUserWishes(int $userId): Collection
    {
        return Wish::whereHas('wishList', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['wishList', 'reservation.user'])->get();
    }

    /**
     * Find wish list by user.
     */
    private function findWishListByUser(int $wishListId, int $userId): WishList
    {
        return WishList::where('id', $wishListId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    /**
     * Clear user cache by wish list ID
     */
    private function clearUserCacheByWishList(int $wishListId): void
    {
        $wishList = WishList::find($wishListId);
        if ($wishList && $wishList->user_id) {
            $this->cacheService->clearUserCache($wishList->user_id);
        }
    }
}
