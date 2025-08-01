<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Wish;
use App\Models\FriendRequest;
use Carbon\Carbon;

class AchievementCheckers
{
    /**
     * Получено при регистрации (всегда true).
     */
    public function checkRegister(User $user): bool
    {
        return true;
    }

    /**
     * Первый подарок (есть хотя бы один wish).
     */
    public function checkGift(User $user): bool
    {
        $wishListIds = $user->wishLists()->pluck('id')->toArray();
        $wishCount = Wish::whereIn('wish_list_id', $wishListIds)->count();
        
        return $wishCount > 0;
    }

    /**
     * Первое бронирование (есть хотя бы одна бронь).
     */
    public function checkReserve(User $user): bool
    {
        return $user->reservations()->count() > 0;
    }

    /**
     * Первый друг (есть хотя бы один друг).
     */
    public function checkFriend(User $user): bool
    {
        $hasFriends = FriendRequest::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('friend_id', $user->id);
        })->where('status', 'accepted')->exists();

        return $hasFriends;
    }

    /**
     * Мастер подарков (50+ добавленных подарков).
     */
    public function checkGiftMaster(User $user): bool
    {
        $wishListIds = $user->wishLists()->pluck('id')->toArray();
        $wishCount = Wish::whereIn('wish_list_id', $wishListIds)->count();
        
        return $wishCount >= 50;
    }

    /**
     * Мастер бронирований (50+ забронированных подарков).
     */
    public function checkReserveMaster(User $user): bool
    {
        return $user->reservations()->count() >= 50;
    }

    /**
     * Душа компании (10+ друзей).
     */
    public function checkSocialButterfly(User $user): bool
    {
        $friendsCount = FriendRequest::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('friend_id', $user->id);
        })->where('status', 'accepted')->count();

        return $friendsCount >= 10;
    }

    /**
     * Ветеран сайта (месяц регистрации на сайте).
     */
    public function checkVeteran(User $user): bool
    {
        $registrationDate = Carbon::parse($user->created_at);
        $oneMonthAgo = Carbon::now()->subMonth();
        
        return $registrationDate->lt($oneMonthAgo);
    }
} 