<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class InvalidAuthGuardException extends RuntimeException
{
    public function __construct(string $message = 'Auth guard must implement StatefulGuard interface')
    {
        parent::__construct($message);
    }
}

