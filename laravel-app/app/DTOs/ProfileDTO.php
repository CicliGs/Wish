<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;

readonly class ProfileDTO implements BaseDTO
{
    public function __construct(
        public User       $user,
        public array      $stats,
        public array      $friends,
        public array      $incomingRequests,
        public array      $outgoingRequests,
        public array      $achievements,
        public array      $wishLists
    ) {}

    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'stats' => $this->stats,
            'friends' => $this->friends,
            'incomingRequests' => $this->incomingRequests,
            'outgoingRequests' => $this->outgoingRequests,
            'achievements' => $this->achievements,
            'wishLists' => $this->wishLists,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            user: $data['user'],
            stats: $data['stats'],
            friends: $data['friends'],
            incomingRequests: $data['incomingRequests'],
            outgoingRequests: $data['outgoingRequests'],
            achievements: $data['achievements'],
            wishLists: $data['wishLists'],
        );
    }

    /**
     * Create DTO from user with all related data.
     */
    public static function fromUserWithData(
        User $user,
        array $stats,
        array $friends,
        array $incomingRequests,
        array $outgoingRequests,
        array $achievements,
        array $wishLists
    ): static {
        return new self(
            user: $user,
            stats: $stats,
            friends: $friends,
            incomingRequests: $incomingRequests,
            outgoingRequests: $outgoingRequests,
            achievements: $achievements,
            wishLists: $wishLists
        );
    }
}
