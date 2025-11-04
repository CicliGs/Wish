<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class NotificationCreationFailedException extends RuntimeException
{
    public function __construct(string $message = 'Failed to create notification')
    {
        parent::__construct($message);
    }
}

