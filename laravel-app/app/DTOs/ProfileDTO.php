<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProfileDTO extends BaseDTO
{
    public function __construct(
        public readonly User $user,
        public readonly array $stats,
        public readonly Collection $friends,
        public readonly Collection $incomingRequests,
        public readonly Collection $outgoingRequests,
        public readonly array $achievements,
        public readonly Collection $wishLists
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
        return new static(
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