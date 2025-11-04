<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class WishCreationFailedException extends RuntimeException
{
    public function __construct(string $message = 'Failed to create wish')
    {
        parent::__construct($message);
    }
}

