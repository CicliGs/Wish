<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class WishListCreationFailedException extends RuntimeException
{
    public function __construct(string $message = 'Failed to create wish list')
    {
        parent::__construct($message);
    }
}

