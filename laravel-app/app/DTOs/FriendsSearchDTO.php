<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Database\Eloquent\Collection;

readonly class FriendsSearchDTO implements BaseDTO
{
    public function __construct(
        public Collection $users,
        public ?string    $query,
        public array      $friendStatuses = []
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
        return new self(
            users: $data['users'],
            query: $data['query'] ?? null,
            friendStatuses: $data['friendStatuses'] ?? [],
        );
    }

    /**
     * Create DTO from search results.
     */
    public static function fromSearchResults(Collection $users, ?string $query = null, array $friendStatuses = []): static
    {
        return new self(
            users: $users,
            query: $query,
            friendStatuses: $friendStatuses
        );
    }

    /**
     * Create empty DTO for empty search.
     */
    public static function empty(): static
    {
        return new self(
            users: new Collection(),
            query: null,
            friendStatuses: []
        );
    }
}
