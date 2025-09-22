<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WishDTO;
use App\DTOs\UserWishesDTO;
use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WishService
{
    private const STORAGE_PATH = 'wishes';

    public function __construct(
        protected CacheManagerService $cacheManager
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
    public function create(array $wishData, int $wishListId): Wish
    {
        $wishData['wish_list_id'] = $wishListId;

        $wish = Wish::create($wishData);

        $this->cacheManager->clearWishListCache($wishListId, Auth::id());

        return $wish;
    }

    /**
     * Update an existing wish.
     */
    public function update(Wish $wish, array $wishData): Wish
    {
        $wish->update($wishData);

        $this->cacheManager->clearWishCache($wish->id, $wish->wish_list_id, Auth::id());

        return $wish->fresh();
    }

    /**
     * Delete a wish.
     */
    public function delete(Wish $wish): bool
    {
        $result = $wish->delete();

        if ($result) {
            $this->cacheManager->clearWishCache($wish->id, $wish->wish_list_id, Auth::id());
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

        $result = $wish->reserveForUser($userId);

        if ($result) {
            $this->cacheManager->clearWishCache($wish->id, $wish->wish_list_id, $userId);
        }

        return $result;
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

        $result = $wish->dereserve();

        if ($result) {
            $this->cacheManager->clearWishCache($wish->id, $wish->wish_list_id, $userId);
        }

        return $result;
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
        $wishLists = WishList::where('user_id', $userId)
            ->withCount('wishes')
            ->get();

        return UserWishesDTO::fromUserWishLists($user, $wishLists);
    }

    /**
     * Get data for specific user wish list page.
     */
    public function getUserWishListData(int $userId, int $wishListId): UserWishesDTO
    {
        $user = User::findOrFail($userId);
        $wishList = $this->findWishListByUser($wishListId, $userId);
        $wishes = $wishList->wishes()->with('reservation.user')->get();

        return UserWishesDTO::fromUserWithSelectedWishList(
            user: $user,
            wishLists: WishList::where('id', $wishList->id)->get(),
            wishes: $wishes,
            selectedWishList: $wishList
        );
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
    public function createWithImage(array $wishData, int $wishListId, ?UploadedFile $imageFile = null): Wish
    {
        if ($imageFile) {
            $imagePath = $this->handleImageUpload($imageFile);

            $wishData['image'] = $imagePath;
            $wishData['wish_list_id'] = $wishListId;

            $wish = Wish::create($wishData);

            $this->cacheManager->clearWishListCache($wishListId, Auth::id());

            return $wish;
        }

        return $this->create($wishData, $wishListId);
    }

    /**
     * Get index data for wish list with caching.
     */
    public function getIndexData(int $wishListId, int $userId): WishDTO
    {
        $cacheKey = "wishes_list_{$wishListId}_user_$userId";

        $cachedData = $this->cacheManager->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        $wishList = WishList::findOrFail($wishListId);
        $wishes = $this->findByWishList($wishListId);
        $stats = $this->getWishListStatistics($wishListId);

        $dto = WishDTO::fromWishListData($wishList, $wishes, $userId, $stats);

        $this->cacheManager->cacheService->cacheStaticContent($cacheKey, serialize($dto), 1800);

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

        return WishDTO::fromWishListData($wishList, $wishes, $userId, $stats);
    }

    /**
     * Get reserved data for wish list.
     */
    public function getReservedData(int $wishListId, int $userId): WishDTO
    {
        $wishList = WishList::findOrFail($wishListId);
        $wishes = $this->getReservedWishes($wishListId);
        $stats = $this->getWishListStatistics($wishListId);

        return WishDTO::fromWishListData($wishList, $wishes, $userId, $stats);
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

}
