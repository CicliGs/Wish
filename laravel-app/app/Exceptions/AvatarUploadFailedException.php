<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class AvatarUploadFailedException extends RuntimeException
{
    public function __construct(string $message = 'Failed to upload avatar')
    {
        parent::__construct($message);
    }
}

