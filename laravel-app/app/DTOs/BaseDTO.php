<?php

declare(strict_types=1);

namespace App\DTOs;

interface BaseDTO
{
    /**
     * Convert DTO to array for view passing.
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Create DTO from array data.
     * 
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static;
} 