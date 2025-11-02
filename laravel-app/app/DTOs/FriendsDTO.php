<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;

readonly class FriendsDTO implements BaseDTO
{
    public function __construct(
        public array      $friends,
        public array      $incomingRequests,
        public array      $outgoingRequests,
        public ?User      $selectedFriend
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
        return new self(
            friends: $data['friends'],
            incomingRequests: $data['incomingRequests'],
            outgoingRequests: $data['outgoingRequests'],
            selectedFriend: $data['selectedFriend'] ?? null,
        );
    }

    /**
     * Create DTO from friends data.
     */
    public static function fromFriendsData(
        array $friends,
        array $incomingRequests,
        array $outgoingRequests,
        ?User $selectedFriend = null
    ): static {
        return new self(
            friends: $friends,
            incomingRequests: $incomingRequests,
            outgoingRequests: $outgoingRequests,
            selectedFriend: $selectedFriend
        );
    }
}
