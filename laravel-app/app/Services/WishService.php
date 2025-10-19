<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WishDTO;
use App\DTOs\UserWishesDTO;
use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use App\Repositories\Contracts\WishRepositoryInterface;
use App\Repositories\Contracts\WishListRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class WishService
{
    private const STORAGE_PATH = 'wishes';

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CacheManagerService $cacheManager,
        protected WishRepositoryInterface $wishRepository,
        protected WishListRepositoryInterface $wishListRepository
    ) {}

    /**
     * Find wishes by wish list.
     */
    public function findWishes(WishList $wishList): Collection
    {
        return $this->wishRepository->findByWishList($wishList);
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
        $wish = $this->wishRepository->create($wishData);

        $this->cacheManager->clearWishListCache($wishList->id, $user->id);

        return $wish;
    }

    /**
     * Update an existing wish.
     */
    public function update(Wish $wish, array $wishData, User $user): Wish
    {
        $wish = $this->wishRepository->update($wish, $wishData);

        $this->cacheManager->clearWishCache($wish->wish_list_id, $user->id);

        return $wish;
    }

    /**
     * Delete a wish.
     */
    public function delete(Wish $wish, User $user): bool
    {
        $result = $this->wishRepository->delete($wish);

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
        return $this->wishRepository->getStatistics($wishList)->toArray();
    }

    /**
     * Get data for user wish lists page.
     */
    public function getWishListsData(User $user): UserWishesDTO
    {
        $wishLists = $this->wishListRepository->findWithWishesCount($user);

        return UserWishesDTO::fromUserWishLists($user, $wishLists);
    }

    /**
     * Get data for specific user wish list page.
     */
    public function getWishListData(User $user, WishList $wishList): UserWishesDTO
    {
        $wishes = $this->wishRepository->findWithReservations($wishList);
        
        $wishLists = $this->wishListRepository->findByUserId($user->id);

        return UserWishesDTO::fromUserWithSelectedWishList(
            user: $user,
            wishLists: $wishLists,
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
            'available' => $this->wishRepository->findAvailableInWishList($wishList),
            'reserved' => $this->wishRepository->findReservedByUser($user)->where('wish_list_id', $wishList->id),
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
