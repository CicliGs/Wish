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
     * Find wishes by wish list.
     */
    public function findWishesByWishList(WishList $wishList): Collection
    {
        return Wish::forWishList($wishList->id)->with('reservation.user')->get();
    }

    /**
     * Create a new wish.
     */
    public function createWish(array $wishData, WishList $wishList): Wish
    {
        $wishData['wish_list_id'] = $wishList->id;

        $wish = Wish::create($wishData);

        $this->cacheManager->clearWishListCache($wishList->id, Auth::id());

        return $wish;
    }

    /**
     * Update an existing wish.
     */
    public function updateWish(Wish $wish, array $wishData): Wish
    {
        $wish->update($wishData);

        $this->cacheManager->clearWishCache($wish->wish_list_id, Auth::id());

        return $wish->fresh();
    }

    /**
     * Delete a wish.
     */
    public function deleteWish(Wish $wish): bool
    {
        $result = $wish->delete();

        if ($result) {
            $this->cacheManager->clearWishCache($wish->wish_list_id, Auth::id());
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
            $this->cacheManager->clearWishCache($wish->wish_list_id, $userId);
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
            $this->cacheManager->clearWishCache($wish->wish_list_id, $userId);
        }

        return $result;
    }

    /**
     * Get available wishes for a wish list.
     */
    public function getAvailableWishes(WishList $wishList): Collection
    {
        return Wish::forWishList($wishList->id)->available()->get();
    }

    /**
     * Get reserved wishes for a wish list.
     */
    public function getReservedWishes(WishList $wishList): Collection
    {
        return Wish::forWishList($wishList->id)->reserved()->with('reservation.user')->get();
    }

    /**
     * Get wish list statistics.
     */
    public function getWishListStatistics(WishList $wishList): array
    {
        $wishes = Wish::forWishList($wishList->id);

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
    public function getUserWishListData(int $userId, WishList $wishList): UserWishesDTO
    {
        $user = User::findOrFail($userId);
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
    public function createWishWithImage(array $wishData, WishList $wishList, ?UploadedFile $imageFile = null): Wish
    {
        if ($imageFile) {
            $imagePath = $this->handleImageUpload($imageFile);

            $wishData['image'] = $imagePath;
            $wishData['wish_list_id'] = $wishList->id;

            $wish = Wish::create($wishData);

            $this->cacheManager->clearWishListCache($wishList->id, Auth::id());

            return $wish;
        }

        return $this->createWish($wishData, $wishList);
    }

    /**
     * Get index data for wish list with caching.
     */
    public function getIndexData(WishList $wishList, int $userId): WishDTO
    {
        $cacheKey = "wishes_list_{$wishList->id}_user_$userId";

        $cachedData = $this->cacheManager->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        $wishes = $this->findWishesByWishList($wishList);
        $stats = $this->getWishListStatistics($wishList);

        $dto = WishDTO::fromWishListData($wishList, $wishes, $userId, $stats);

        $this->cacheManager->cacheService->cacheStaticContent($cacheKey, serialize($dto), 1800);

        return $dto;
    }

    /**
     * Get available data for wish list.
     */
    public function getAvailableData(WishList $wishList, int $userId): WishDTO
    {
        $wishes = $this->getAvailableWishes($wishList);
        $stats = $this->getWishListStatistics($wishList);

        return WishDTO::fromWishListData($wishList, $wishes, $userId, $stats);
    }

    /**
     * Get reserved data for wish list.
     */
    public function getReservedData(WishList $wishList, int $userId): WishDTO
    {
        $wishes = $this->getReservedWishes($wishList);
        $stats = $this->getWishListStatistics($wishList);

        return WishDTO::fromWishListData($wishList, $wishes, $userId, $stats);
    }

}
