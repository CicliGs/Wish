<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Wish;
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

    private function validateCreateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'url', 'max:500'],
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
            'image' => ['nullable', 'url', 'max:500'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
