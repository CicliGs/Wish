<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class WishUpdateFailedException extends RuntimeException
{
    public function __construct(string $message = 'Failed to update wish')
    {
        parent::__construct($message);
    }
}

