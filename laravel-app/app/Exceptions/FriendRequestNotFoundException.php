<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class FriendRequestNotFoundException extends RuntimeException
{
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? __('messages.friend_request_not_found'));
    }
}

