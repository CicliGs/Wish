<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class CannotAddSelfAsFriendException extends RuntimeException
{
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? __('messages.cannot_add_self_as_friend'));
    }
}

