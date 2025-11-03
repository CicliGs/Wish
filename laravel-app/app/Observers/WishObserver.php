<?php

declare(strict_types=1);

namespace App\Observers;

use App\DTOs\NotificationDTO;
use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use App\Services\FriendService;
use App\Services\NotificationJobDispatcher;
use App\Repositories\Contracts\WishListRepositoryInterface;
use Exception;
use Psr\Log\LoggerInterface;

readonly class WishObserver
{
    public function __construct(
        private FriendService $friendService,
        private NotificationJobDispatcher $jobDispatcher,
        private WishListRepositoryInterface $wishListRepository,
        private LoggerInterface $logger
    ) {}

    /**
     * Handle the Wish "created" event.
     */
    public function created(Wish $wish): void
    {
        try {
            $this->notifyFriendsAboutNewWish($wish);
        } catch (Exception $e) {
            $this->logger->error('WishObserver: Error in created method', [
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
            $wishList = $this->wishListRepository->findById($wish->wish_list_id);
            if (!$wishList || !($wishList instanceof WishList)) {
                $this->logger->warning('WishObserver: WishList not found for wish', ['wish_id' => $wish->id]);
                return;
            }

            $user = $this->wishListRepository->findUserForWishList($wishList);
            if (!$user || !($user instanceof User)) {
                $this->logger->warning('WishObserver: User not found for wishList', [
                    'wish_id' => $wish->id,
                    'wish_list_id' => $wishList->id
                ]);
                return;
            }

            $friends = $this->friendService->getFriends($user);
            if ($friends->isEmpty()) {
                return;
            }

            /** @var User $friend */
            foreach ($friends as $friend) {
                $message = __('messages.friend_added_new_wish', [
                    'friendName' => $user->name,
                    'wishTitle' => $wish->title
                ]);

                $notificationDTO = NotificationDTO::forNewWish(
                    userId: $friend->id,
                    friendId: $user->id,
                    wishId: $wish->id,
                    friendName: $user->name,
                    wishTitle: $wish->title,
                    message: $message
                );

                $this->jobDispatcher->dispatch($notificationDTO);
            }
        } catch (Exception $e) {
            $this->logger->error('WishObserver: Error in notifyFriendsAboutNewWish', [
                'wish_id' => $wish->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
