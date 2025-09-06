<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WishListDTO;
use App\DTOs\PublicWishListDTO;
use App\Models\WishList;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class WishListService
{
    private const MAX_TITLE_LENGTH = 255;
    private const MAX_DESCRIPTION_LENGTH = 1000;

    public function __construct(
        protected CacheService $cacheService
    ) {}

    public function findByUser(int $userId): Collection
    {
        return WishList::forUser($userId)->with('wishes')->get();
    }

    public function findByIdAndUser(int $id, int $userId): ?WishList
    {
        return WishList::forUser($userId)->find($id);
    }

    /**
     * @throws ValidationException
     */
    public function create(array $data, int $userId): WishList
    {
        $this->validateCreateData($data);
        $data['user_id'] = $userId;

        $wishList = WishList::create($data);
        $this->cacheService->clearUserCache($userId);

        return $wishList;
    }

    /**
     * @throws ValidationException
     */
    public function update(WishList $wishList, array $data): WishList
    {
        $this->validateUpdateData($data);
        
        $wasPublic = $wishList->is_public;
        $willBePublic = $data['is_public'] ?? $wasPublic;
        
        $wishList->update($data);
        $this->cacheService->clearUserCache($wishList->user_id);
        
        if ($wasPublic !== $willBePublic && $wishList->uuid) {
            $publicCacheKey = "public_wishlist_" . $wishList->uuid;
            Cache::forget("static_content:" . $publicCacheKey);
        }

        return $wishList->fresh();
    }

    public function delete(WishList $wishList): bool
    {
        $userId = $wishList->user_id;
        
        try {
            $result = $wishList->delete();

            if ($result) {
                $this->cacheService->clearUserCache($userId);
                
                if ($wishList->uuid) {
                    $publicCacheKey = "public_wishlist_" . $wishList->uuid;
                    Cache::forget("static_content:" . $publicCacheKey);
                }
            }

            return $result;
        } catch (\Exception $e) {
            \Log::error('Error deleting wish list', [
                'wish_list_id' => $wishList->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function findPublic(string $uuid): ?WishList
    {
        return WishList::public()->where('uuid', $uuid)->with('wishes')->first();
    }

    public function regenerateUuid(WishList $wishList): WishList
    {
        $wishList->update(['uuid' => (string) Str::uuid()]);
        return $wishList->fresh();
    }

    public function getStatistics(int $userId): array
    {
        $wishLists = $this->findByUser($userId);

        return [
            'total_wish_lists' => $wishLists->count(),
            'total_wishes' => $wishLists->sum(fn($wishList) => $wishList->wishes->count()),
            'total_reserved_wishes' => $wishLists->sum(fn($wishList) => $wishList->wishes->where('is_reserved', true)->count()),
            'public_wish_lists' => $wishLists->whereNotNull('uuid')->count(),
        ];
    }

    public function getPublicWishListData(string $uuid): PublicWishListDTO
    {
        $cacheKey = "public_wishlist_$uuid";
        $cachedData = $this->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        $wishList = $this->findPublic($uuid);

        if (!$wishList) {
            throw new ModelNotFoundException();
        }

        $currentUser = auth()->user();
        $dto = new PublicWishListDTO(
            wishList: $wishList,
            user: $wishList->user,
            wishes: $wishList->wishes,
            isGuest: !auth()->check(),
            isFriend: $currentUser && $currentUser->friends()->where('friend_id', $wishList->user_id)->exists(),
            isOwner: auth()->id() === $wishList->user_id
        );

        $this->cacheService->cacheStaticContent($cacheKey, serialize($dto), 1800);
        return $dto;
    }

    public function getIndexData(int $userId): WishListDTO
    {
        $cacheKey = "user_wishlists_$userId";
        $cachedData = $this->cacheService->getStaticContent($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData);
        }

        $wishLists = $this->findByUser($userId);
        $stats = $this->getStatistics($userId);

        $dto = new WishListDTO(
            wishLists: $wishLists,
            stats: $stats,
            userId: $userId
        );

        $this->cacheService->cacheStaticContent($cacheKey, serialize($dto), 3600);
        return $dto;
    }

    /**
     * @throws ValidationException
     */
    private function validateCreateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:' . self::MAX_TITLE_LENGTH],
            'description' => ['nullable', 'string', 'max:' . self::MAX_DESCRIPTION_LENGTH],
            'is_public' => ['boolean'],
            'currency' => ['required', 'string', 'in:' . implode(',', WishList::getSupportedCurrencies())],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateUpdateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['sometimes', 'required', 'string', 'max:' . self::MAX_TITLE_LENGTH],
            'description' => ['sometimes', 'nullable', 'string', 'max:' . self::MAX_DESCRIPTION_LENGTH],
            'is_public' => ['sometimes', 'boolean'],
            'currency' => ['sometimes', 'required', 'string', 'in:' . implode(',', WishList::getSupportedCurrencies())],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
