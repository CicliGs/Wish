<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class FriendsDTO extends BaseDTO
{
    public function __construct(
        public readonly Collection $friends,
        public readonly Collection $incomingRequests,
        public readonly Collection $outgoingRequests,
        public readonly ?User $selectedFriend
    ) {}

    public function toArray(): array
    {
        return [
            'friends' => $this->friends,
            'incomingRequests' => $this->incomingRequests,
            'outgoingRequests' => $this->outgoingRequests,
            'selectedFriend' => $this->selectedFriend,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            friends: $data['friends'],
            incomingRequests: $data['incomingRequests'],
            outgoingRequests: $data['outgoingRequests'],
            selectedFriend: $data['selectedFriend'] ?? null,
        );
    }
} 