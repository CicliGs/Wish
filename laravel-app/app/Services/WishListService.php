<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WishListDTO;
use App\DTOs\PublicWishListDTO;
use App\Models\WishList;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class WishListService
{
    private const MAX_TITLE_LENGTH = 255;
    private const MAX_DESCRIPTION_LENGTH = 1000;

    /**
     * Find wish lists by user ID.
     */
    public function findByUser(int $userId): Collection
    {
        return WishList::forUser($userId)->with('wishes')->get();
    }

    /**
     * Find a wish list by ID and user ID.
     */
    public function findByIdAndUser(int $id, int $userId): ?WishList
    {
        return WishList::forUser($userId)->find($id);
    }

    /**
     * Create a new wish list.
     */
    public function create(array $data, int $userId): WishList
    {
        $this->validateCreateData($data);
        $data['user_id'] = $userId;

        return WishList::create($data);
    }

    /**
     * Update an existing wish list.
     */
    public function update(WishList $wishList, array $data): WishList
    {
        $this->validateUpdateData($data, $wishList);
        $wishList->update($data);

        return $wishList->fresh();
    }

    /**
     * Delete a wish list.
     */
    public function delete(WishList $wishList): bool
    {
        return $wishList->delete();
    }

    /**
     * Find public wish list by UUID.
     */
    public function findPublic(string $uuid): ?WishList
    {
        return WishList::public()->where('uuid', $uuid)->with('wishes')->first();
    }

    /**
     * Generate a new UUID for a wish list.
     */
    public function regenerateUuid(WishList $wishList): WishList
    {
        $wishList->update(['uuid' => (string) Str::uuid()]);

        return $wishList->fresh();
    }

    /**
     * Get user statistics.
     */
    public function getStatistics(int $userId): array
    {
        $wishLists = WishList::forUser($userId)->with('wishes')->get();

        return [
            'total_wish_lists' => $wishLists->count(),
            'total_wishes' => $wishLists->sum('wishes_count'),
            'total_reserved_wishes' => $wishLists->sum('reserved_wishes_count'),
            'public_wish_lists' => $wishLists->whereNotNull('uuid')->count(),
        ];
    }

    /**
     * Get data for public wish list view.
     */
    public function getPublicWishListData(string $uuid): PublicWishListDTO
    {
        $wishList = $this->findPublic($uuid);
        
        if (!$wishList) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }

        return new PublicWishListDTO(
            wishList: $wishList,
            wishes: $wishList->wishes,
            user: $wishList->user
        );
    }

    /**
     * Get index data for wish lists.
     */
    public function getIndexData(int $userId): WishListDTO
    {
        $wishLists = $this->findByUser($userId);
        $stats = $this->getStatistics($userId);

        return new WishListDTO(
            wishLists: $wishLists,
            stats: $stats,
            userId: $userId
        );
    }

    /**
     * Validate create data.
     */
    private function validateCreateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:' . self::MAX_TITLE_LENGTH],
            'description' => ['nullable', 'string', 'max:' . self::MAX_DESCRIPTION_LENGTH],
            'is_public' => ['boolean'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate update data.
     */
    private function validateUpdateData(array $data, WishList $wishList): void
    {
        $validator = Validator::make($data, [
            'title' => ['sometimes', 'required', 'string', 'max:' . self::MAX_TITLE_LENGTH],
            'description' => ['sometimes', 'nullable', 'string', 'max:' . self::MAX_DESCRIPTION_LENGTH],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
