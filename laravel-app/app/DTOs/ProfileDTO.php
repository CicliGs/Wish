<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

readonly class ProfileDTO implements BaseDTO
{
    public function __construct(
        public User       $user,
        public array      $stats,
        public Collection $friends,
        public Collection $incomingRequests,
        public Collection $outgoingRequests,
        public array      $achievements,
        public Collection $wishLists
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
}
