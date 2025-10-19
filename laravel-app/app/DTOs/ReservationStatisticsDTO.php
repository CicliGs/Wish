<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ReservationStatisticsDTO implements BaseDTO
{
    public function __construct(
        public int $totalReservations,
        public float $totalValue,
        public float $averagePrice,
        public int $uniqueUsers = 0
    ) {}

    public function toArray(): array
    {
        return [
            'total_reservations' => $this->totalReservations,
            'total_value' => $this->totalValue,
            'average_price' => $this->averagePrice,
            'unique_users' => $this->uniqueUsers,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            totalReservations: $data['total_reservations'] ?? 0,
            totalValue: $data['total_value'] ?? 0.0,
            averagePrice: $data['average_price'] ?? 0.0,
            uniqueUsers: $data['unique_users'] ?? 0,
        );
    }

    /**
     * Create DTO from reservation statistics array.
     */
    public static function fromReservationStats(array $stats): static
    {
        return new self(
            totalReservations: $stats['total_reservations'] ?? 0,
            totalValue: $stats['total_value'] ?? 0.0,
            averagePrice: $stats['average_price'] ?? 0.0,
            uniqueUsers: $stats['unique_users'] ?? 0,
        );
    }
}
