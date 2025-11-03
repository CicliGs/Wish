<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WishListDTO;
use App\DTOs\PublicWishListDTO;
use App\Models\User;
use App\Models\WishList;
use App\Repositories\Contracts\WishListRepositoryInterface;
use App\Repositories\Contracts\WishRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WishListService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CacheManagerService $cacheManager,
        protected WishListRepositoryInterface $wishListRepository,
        protected WishRepositoryInterface $wishRepository,
        protected ?FriendService $friendService = null
    ) {}

    /**
     * Find wish lists by user.
     */
    public function findWishLists(User $user): Collection
    {
        return collect($this->wishListRepository->findByUser($user));
    }

    /**
     * Create a new wish list.
     */
    public function create(array $data, User $user): WishList
    {
        $data['user_id'] = $user->id;

        $wishList = $this->wishListRepository->create($data);
        if (!$wishList instanceof WishList) {
            throw new \RuntimeException('Failed to create wish list');
        }
        $this->cacheManager->clearUserCache($user->id);

        return $wishList;
    }

    /**
     * Update an existing wish list.
     */
    public function update(WishList $wishList, array $data): WishList
    {
        $wasPublic = $wishList->is_public;
        $willBePublic = $data['is_public'] ?? $wasPublic;

        $updatedWishList = $this->wishListRepository->update($wishList, $data);
        if (!$updatedWishList instanceof WishList) {
            throw new \RuntimeException('Failed to update wish list');
        }
        
        if (isset($updatedWishList->user_id)) {
            $this->cacheManager->clearUserCache($updatedWishList->user_id);
        }

        if ($wasPublic !== $willBePublic && isset($updatedWishList->uuid)) {
            $this->cacheManager->clearPublicWishListCache($updatedWishList->uuid);
        }

        return $updatedWishList;
    }

    /**
     * Delete a wish list and clear related caches.
     */
    public function delete(WishList $wishList): bool
    {
        $this->clearRelatedCaches($wishList);

        return $this->wishListRepository->delete($wishList);
    }

    /**
     * Find public wish list by UUID.
     */
    public function findPublicByUuid(string $uuid): ?WishList
    {
        return $this->wishListRepository->findPublicByUuid($uuid);
    }

    /**
     * Get user statistics.
     */
    public function getStatistics(User $user): array
    {
        return $this->wishListRepository->getStatistics($user)->toArray();
    }

    /**
     * Get public wish list data.
     */
    public function getPublicData(string $uuid, ?User $currentUser = null): PublicWishListDTO
    {
        $wishList = $this->findPublicByUuid($uuid);

        if (!$wishList) {
            throw new ModelNotFoundException();
        }

        $user = $this->wishListRepository->findUserForWishList($wishList);
        if (!$user) {
            throw new ModelNotFoundException('User not found for wish list');
        }

        $wishes = $this->wishRepository->findByWishList($wishList);

        $isGuest = $currentUser === null;
        $isOwner = $currentUser && $currentUser->id === $wishList->user_id;
        
        $isFriend = false;
        if ($currentUser && $this->friendService && !$isOwner) {
            $friends = $this->friendService->getFriends($currentUser);
            $isFriend = $friends->contains('id', $wishList->user_id);
        }

        if (!$user instanceof User) {
            throw new ModelNotFoundException('User must be an instance of ' . User::class);
        }
        
        return PublicWishListDTO::fromData($wishList, $user, $wishes, $isGuest, $isFriend, $isOwner);
    }

    /**
     * Get index data with caching.
     */
    public function getIndexData(User $user): WishListDTO
    {
        $cacheKey = "user_wishlists_{$user->id}";
        $cachedData = $this->cacheManager->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            try {
                $dto = unserialize($cachedData);
                if ($dto instanceof WishListDTO) {
                    $wishListsValue = $dto->wishLists ?? null;
                    if (is_array($wishListsValue)) {
                        // Check if array contains collections (old cached data)
                        $needsRegeneration = false;
                        foreach ($wishListsValue as $item) {
                            if (is_object($item)) {
                                $className = get_class($item);
                                if (str_contains($className, 'Collection')) {
                                    $needsRegeneration = true;
                                    break;
                                }
                            }
                        }
                        if ($needsRegeneration) {
                            $wishLists = $this->wishListRepository->findWithWishesCount($user);
                            $stats = $this->getStatistics($user);
                            $dto = WishListDTO::fromWishLists($wishLists, $user->id, $stats);
                            $this->cacheManager->cacheService->cacheStaticContent($cacheKey, serialize($dto), 3600);
                            return $dto;
                        }
                    }
                    return $dto;
                }
            } catch (\TypeError $e) {
                // Invalid cached data, regenerate
            }
        }

        $wishLists = $this->wishListRepository->findWithWishesCount($user);
        $stats = $this->getStatistics($user);

        $dto = WishListDTO::fromWishLists($wishLists, $user->id, $stats);

        $this->cacheManager->cacheService->cacheStaticContent($cacheKey, serialize($dto), 3600);

        return $dto;
    }

    /**
     * Clear caches related to the wish list.
     */
    private function clearRelatedCaches(WishList $wishList): void
    {
        $this->cacheManager->clearUserCache($wishList->user_id);
        $this->cacheManager->clearWishListCache($wishList->id, $wishList->user_id);

        if ($wishList->uuid) {
            $this->cacheManager->clearPublicWishListCache($wishList->uuid);
        }
    }
}
