<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

readonly class FriendsDTO implements BaseDTO
{
    public function __construct(
        public Collection $friends,
        public Collection $incomingRequests,
        public Collection $outgoingRequests,
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
}
