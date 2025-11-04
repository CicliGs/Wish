<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class UserCreationFailedException extends RuntimeException
{
    public function __construct(string $message = 'Failed to create user')
    {
        parent::__construct($message);
    }
}

