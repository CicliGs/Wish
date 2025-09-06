<?php

declare(strict_types=1);

namespace App\Services;

enum FriendRequestStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Ожидает ответа',
            self::ACCEPTED => 'Принят',
            self::DECLINED => 'Отклонен',
        };
    }
}
