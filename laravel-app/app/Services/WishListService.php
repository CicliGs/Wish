<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WishList;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class WishListService
{
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

    public function create(array $data, int $userId): WishList
    {
        $this->validateCreateData($data);
        $data['user_id'] = $userId;

        return WishList::create($data);
    }

    public function update(WishList $wishList, array $data): WishList
    {
        $this->validateUpdateData($data, $wishList);
        $wishList->update($data);

        return $wishList->fresh();
    }

    public function delete(WishList $wishList): bool
    {
        return $wishList->delete();
    }

    public function findPublic(string $publicId): ?WishList
    {
        return WishList::public()->where('public_id', $publicId)->with('wishes')->first();
    }

    /**
     * Generate a new public ID for a wish list.
     */
    public function regeneratePublicId(WishList $wishList): WishList
    {
        $wishList->update(['public_id' => (string) \Illuminate\Support\Str::uuid()]);

        return $wishList->fresh();
    }

    public function getStatistics(int $userId): array
    {
        $wishLists = WishList::forUser($userId)->with('wishes')->get();

        return [
            'total_wish_lists' => $wishLists->count(),
            'total_wishes' => $wishLists->sum('wishes_count'),
            'total_reserved_wishes' => $wishLists->sum('reserved_wishes_count'),
            'public_wish_lists' => $wishLists->whereNotNull('public_id')->count(),
        ];
    }

    /**
     * Получает данные для публичного просмотра списка желаний.
     */
    public function getPublicWishListData(string $publicId): array
    {
        $wishList = $this->findPublic($publicId);

        if (!$wishList) {
            abort(404, __('Список желаний не найден'));
        }

        $wishes = $wishList->wishes;
        $user = $wishList->user;
        
        // Логика для модальных окон
        $isGuest = !auth()->check();
        $isFriend = false;
        if (auth()->check()) {
            $currentUser = auth()->user();
            $isFriend = app(\App\Services\FriendService::class)->isAlreadyFriendOrRequested($currentUser, $user->id);
        }

        return [
            'wishList' => $wishList,
            'wishes' => $wishes,
            'user' => $user,
            'isGuest' => $isGuest,
            'isFriend' => $isFriend
        ];
    }

    /**
     * Получает данные для главной страницы списков желаний.
     */
    public function getIndexData(int $userId): array
    {
        $wishLists = $this->findByUser($userId);
        $statistics = $this->getStatistics($userId);

        return [
            'wishLists' => $wishLists,
            'statistics' => $statistics
        ];
    }

    private function validateCreateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function validateUpdateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
