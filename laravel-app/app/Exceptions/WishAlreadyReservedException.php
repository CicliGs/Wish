<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class WishAlreadyReservedException extends RuntimeException
{
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? __('messages.wish_already_reserved'));
    }
}

