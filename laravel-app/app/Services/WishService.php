<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WishDTO;
use App\DTOs\UserWishesDTO;
use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class WishService
{
    private const MAX_FILE_SIZE = 2048;
    private const STORAGE_PATH = 'wishes';

    /**
     * Find wishes by wish list ID.
     */
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

    /**
     * Create a new wish.
     */
    public function create(array $data, int $wishListId): Wish
    {
        $this->validateCreateData($data);
        $data['wish_list_id'] = $wishListId;

        return Wish::create($data);
    }

    /**
     * Update an existing wish.
     */
    public function update(Wish $wish, array $data): Wish
    {
        $this->validateUpdateData($data);
        $wish->update($data);

        return $wish->fresh();
    }

    /**
     * Delete a wish.
     */
    public function delete(Wish $wish): bool
    {
        return $wish->delete();
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
    public function createWithImage(array $data, int $wishListId, ?UploadedFile $imageFile = null): void
    {
        if ($imageFile) {
            $data['image'] = $this->handleImageUpload($imageFile);
        }
        
        $this->create($data, $wishListId);
    }

    /**
     * Get index data for wish list.
     */
    public function getIndexData(int $wishListId, int $userId): WishDTO
    {
        $wishList = WishList::findOrFail($wishListId);
        $wishes = $this->findByWishList($wishListId);
        $stats = $this->getWishListStatistics($wishListId);

        return new WishDTO(
            wishList: $wishList,
            wishes: $wishes,
            stats: $stats,
            userId: $userId
        );
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
     * Validate create data.
     */
    private function validateCreateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'string', 'max:500'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate update data.
     */
    private function validateUpdateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'image' => ['sometimes', 'nullable', 'string', 'max:500'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
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
