<?php

declare(strict_types=1);

namespace App\Observers;

use App\DTOs\NotificationDTO;
use App\Jobs\ProcessNotificationJob;
use App\Models\Wish;
use App\Services\FriendService;
use Exception;
use Illuminate\Support\Facades\Log;

readonly class WishObserver
{
    public function __construct(
        private FriendService $friendService
    ) {}

    /**
     * Handle the Wish "created" event.
     */
    public function created(Wish $wish): void
    {
        try {
            $this->notifyFriendsAboutNewWish($wish);
        } catch (Exception $e) {
            Log::error('WishObserver: Error in created method', [
                'wish_id' => $wish->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Notify friends about new wish/gift
     */
    private function notifyFriendsAboutNewWish(Wish $wish): void
    {
        try {
            $wishList = $wish->wishList;
            if (!$wishList) {
                Log::warning('WishObserver: WishList not found for wish', ['wish_id' => $wish->id]);
                return;
            }

            $user = $wishList->user;
            if (!$user) {
                Log::warning('WishObserver: User not found for wishList', [
                    'wish_id' => $wish->id,
                    'wish_list_id' => $wishList->id
                ]);
                return;
            }

            $friends = $this->friendService->getFriendsForUser($user);
            if ($friends->isEmpty()) {
                return;
            }

            foreach ($friends as $friend) {
                $notificationDTO = NotificationDTO::forNewWish(
                    userId: $friend->id,
                    friendId: $user->id,
                    wishId: $wish->id,
                    friendName: $user->name,
                    wishTitle: $wish->title
                );

                ProcessNotificationJob::dispatch($notificationDTO);
            }
        } catch (Exception $e) {
            Log::error('WishObserver: Error in notifyFriendsAboutNewWish', [
                'wish_id' => $wish->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
