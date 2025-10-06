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

class WishService
{
    private const STORAGE_PATH = 'wishes';

    public function __construct(
        protected CacheManagerService $cacheManager
    ) {}

    /**
     * Find wishes by wish list.
     */
    public function findWishes(WishList $wishList): Collection
    {
        return Wish::forWishList($wishList->id)->with('reservation.user')->get();
    }

    /**
     * Create a new wish.
     */
    public function create(array $wishData, WishList $wishList, User $user, ?UploadedFile $imageFile = null): Wish
    {
        if ($imageFile) {
            $wishData['image'] = $this->uploadImage($imageFile);
        }

        $wishData['wish_list_id'] = $wishList->id;
        $wish = Wish::create($wishData);

        $this->cacheManager->clearWishListCache($wishList->id, $user->id);

        return $wish;
    }

    /**
     * Update an existing wish.
     */
    public function update(Wish $wish, array $wishData, User $user): Wish
    {
        $wish->update($wishData);

        $this->cacheManager->clearWishCache($wish->wish_list_id, $user->id);

        return $wish->fresh();
    }

    /**
     * Delete a wish.
     */
    public function delete(Wish $wish, User $user): bool
    {
        $result = $wish->delete();

        if ($result) {
            $this->cacheManager->clearWishCache($wish->wish_list_id, $user->id);
        }

        return $result;
    }

    /**
     * Get wish list statistics.
     */
    public function getStatistics(WishList $wishList): array
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
    public function getWishListsData(User $user): UserWishesDTO
    {
        $wishLists = WishList::where('user_id', $user->id)
            ->withCount('wishes')
            ->get();

        return UserWishesDTO::fromUserWishLists($user, $wishLists);
    }

    /**
     * Get data for specific user wish list page.
     */
    public function getWishListData(User $user, WishList $wishList): UserWishesDTO
    {
        $wishes = $wishList->wishes()->with('reservation.user')->get();

        return UserWishesDTO::fromUserWithSelectedWishList(
            user: $user,
            wishLists: WishList::where('id', $wishList->id)->get(),
            wishes: $wishes,
            selectedWishList: $wishList
        );
    }

    /**
     * Get index data for wish list with caching.
     */
    public function getIndexData(WishList $wishList, User $user): WishDTO
    {
        $cacheKey = "wishes_list_{$wishList->id}_user_{$user->id}";

        $cachedData = $this->cacheManager->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        $wishes = $this->findWishes($wishList);
        $stats = $this->getStatistics($wishList);

        $dto = WishDTO::fromWishListData($wishList, $wishes, $user->id, $stats);

        $this->cacheManager->cacheService->cacheStaticContent($cacheKey, serialize($dto), 1800);

        return $dto;
    }

    /**
     * Get wish list data with optional filter.
     */
    public function getData(WishList $wishList, User $user, ?string $filter = null): WishDTO
    {
        $wishes = match ($filter) {
            'available' => Wish::forWishList($wishList->id)->available()->get(),
            'reserved' => Wish::forWishList($wishList->id)->reserved()->with('reservation.user')->get(),
            default => $this->findWishes($wishList)
        };

        $stats = $this->getStatistics($wishList);

        return WishDTO::fromWishListData($wishList, $wishes, $user->id, $stats);
    }

    /**
     * Handle image upload.
     */
    private function uploadImage(UploadedFile $file): string
    {
        $path = $file->store(self::STORAGE_PATH, 'public');
        return '/storage/' . $path;
    }

}
