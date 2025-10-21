<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class WishStatisticsDTO implements BaseDTO
{
    public function __construct(
        public int $totalWishes,
        public int $reservedWishes,
        public int $availableWishes,
        public float $totalPrice,
        public ?float $averagePrice
    ) {}

    public function toArray(): array
    {
        return [
            'total_wishes' => $this->totalWishes,
            'reserved_wishes' => $this->reservedWishes,
            'available_wishes' => $this->availableWishes,
            'total_price' => $this->totalPrice,
            'average_price' => $this->averagePrice,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            totalWishes: $data['total_wishes'] ?? 0,
            reservedWishes: $data['reserved_wishes'] ?? 0,
            availableWishes: $data['available_wishes'] ?? 0,
            totalPrice: $data['total_price'] ?? 0.0,
            averagePrice: $data['average_price'] ?? null,
        );
    }
}
