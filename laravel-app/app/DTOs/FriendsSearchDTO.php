<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Database\Eloquent\Collection;

class FriendsSearchDTO extends BaseDTO
{
    public function __construct(
        public readonly Collection $users,
        public readonly ?string $query,
        public readonly array $friendStatuses = []
    ) {}

    public function toArray(): array
    {
        return [
            'users' => $this->users,
            'query' => $this->query,
            'friendStatuses' => $this->friendStatuses,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            users: $data['users'],
            query: $data['query'] ?? null,
            friendStatuses: $data['friendStatuses'] ?? [],
        );
    }
} 