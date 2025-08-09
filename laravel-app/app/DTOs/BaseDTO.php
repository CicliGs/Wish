<?php

declare(strict_types=1);

namespace App\DTOs;

abstract class BaseDTO
{
    /**
     * Convert DTO to array for view passing.
     */
    abstract public function toArray(): array;

    /**
     * Create DTO from array data.
     */
    abstract public static function fromArray(array $data): static;
} 