<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class WishListUpdateFailedException extends RuntimeException
{
    public function __construct(string $message = 'Failed to update wish list')
    {
        parent::__construct($message);
    }
}

