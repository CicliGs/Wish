<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Wish;
use App\Models\WishList;
use App\DTOs\ReservationStatisticsDTO;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Repositories\Contracts\WishRepositoryInterface;

/**
 * Reservation repository implementation
 */
class ReservationRepository extends BaseRepository implements ReservationRepositoryInterface
{
    /**
     * Create a new repository instance
     */
    public function __construct(
        Reservation $model,
        private readonly WishRepositoryInterface $wishRepository
    ) {
        parent::__construct($model);
    }

    /**
     * Find reservation by wish and user
     */
    public function findByWishAndUser(object $wish, object $user): ?object
    {
        if (!$wish instanceof Wish) {
            throw new \InvalidArgumentException('Wish must be an instance of ' . Wish::class);
        }
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model->where('wish_id', $wish->id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Find reservations by user
     * @return array<object>
     */
    public function findByUser(object $user): array
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model->where('user_id', $user->id)
            ->with(['wish.wishList', 'wish.wishList.user'])
            ->get()
            ->all();
    }

    /**
     * Find reservations by wish list
     * @return array<object>
     */
    public function findByWishList(object $wishList): array
    {
        if (!$wishList instanceof WishList) {
            throw new \InvalidArgumentException('WishList must be an instance of ' . WishList::class);
        }
        return $this->model->whereHas('wish', function ($query) use ($wishList) {
            $query->where('wish_list_id', $wishList->id);
        })->with(['wish', 'user'])->get()->all();
    }

    /**
     * Find reservations by wish
     * @return array<object>
     */
    public function findByWish(object $wish): array
    {
        if (!$wish instanceof Wish) {
            throw new \InvalidArgumentException('Wish must be an instance of ' . Wish::class);
        }
        return $this->model->where('wish_id', $wish->id)
            ->with('user')
            ->get()
            ->all();
    }

    /**
     * Find reservations with related data
     * @return array<object>
     */
    public function findWithRelations(object $entity): array
    {
        if ($entity instanceof User) {
            return $this->findByUser($entity);
        }

        if ($entity instanceof WishList) {
        return $this->findByWishList($entity);
        }

        throw new \InvalidArgumentException('Entity must be an instance of ' . User::class . ' or ' . WishList::class);
    }

    /**
     * Get reservation statistics
     */
    public function getStatistics(object $entity): ReservationStatisticsDTO
    {
        if ($entity instanceof User) {
            $reservations = $this->findByUser($entity);
            
            $totalValue = 0;
            foreach ($reservations as $reservation) {
                if (!$reservation instanceof Reservation) {
                    continue;
                }
                $wishId = $reservation->wish_id ?? null;
                if (!$wishId) {
                    continue;
                }
                $wish = $this->wishRepository->findById($wishId);
                if ($wish instanceof Wish) {
                    $price = $wish->price ?? null;
                    if ($price !== null) {
                        $totalValue += (float) $price;
                    }
                }
            }
            
            $totalReservations = count($reservations);
            $averagePrice = $totalReservations > 0 ? $totalValue / $totalReservations : 0.0;
            
            return new ReservationStatisticsDTO(
                totalReservations: $totalReservations,
                totalValue: $totalValue,
                averagePrice: $averagePrice,
            );
        }

        if ($entity instanceof WishList) {
        $reservations = $this->findByWishList($entity);
            
            $totalValue = 0;
            $userIds = [];
            foreach ($reservations as $reservation) {
                if (!$reservation instanceof Reservation) {
                    continue;
                }
                $wish = $this->wishRepository->findById($reservation->wish_id);
                if ($wish instanceof \App\Models\Wish && isset($wish->price)) {
                    $totalValue += (float) $wish->price;
                }
                if (isset($reservation->user_id)) {
                    $userIds[] = $reservation->user_id;
                }
            }
            
            $totalReservations = count($reservations);
            $averagePrice = $totalReservations > 0 ? $totalValue / $totalReservations : 0.0;
            
        return new ReservationStatisticsDTO(
                totalReservations: $totalReservations,
                totalValue: $totalValue,
                averagePrice: $averagePrice,
                uniqueUsers: count(array_unique($userIds)),
            );
        }

        throw new \InvalidArgumentException('Entity must be an instance of ' . User::class . ' or ' . WishList::class);
    }

    /**
     * Check if wish is reserved by user
     */
    public function isReservedByUser(object $wish, object $user): bool
    {
        if (!$wish instanceof Wish) {
            throw new \InvalidArgumentException('Wish must be an instance of ' . Wish::class);
        }
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model->where('wish_id', $wish->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Count reservations for user
     */
    public function countByUser(object $user): int
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }
        return $this->model->where('user_id', $user->id)->count();
    }

    /**
     * Count reservations for wish list
     */
    public function countByWishList(object $wishList): int
    {
        if (!$wishList instanceof WishList) {
            throw new \InvalidArgumentException('WishList must be an instance of ' . WishList::class);
        }
        return $this->model->whereHas('wish', function ($query) use ($wishList) {
            $query->where('wish_list_id', $wishList->id);
        })->count();
    }
}
