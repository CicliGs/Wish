<?php

declare(strict_types=1);

namespace App\Enums;

enum FriendRequestStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';

    public function label(): string
    {
        return match($this) {
            self::PENDING => __('messages.friend_status_pending'),
            self::ACCEPTED => __('messages.friend_status_accepted'),
            self::DECLINED => __('messages.friend_status_declined'),
        };
    }
}

