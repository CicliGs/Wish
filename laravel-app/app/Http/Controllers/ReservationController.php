<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Wish;
use App\Services\ReservationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class ReservationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ReservationService $service
    ) {}

    /**
     * Reserve a wish.
     * @throws AuthorizationException
     */
    public function reserve(int $wishId): RedirectResponse
    {
        $wish = $this->findWish($wishId);
        $this->authorize('reserve', $wish);

        $result = $this->service->reserveWishForUser($wish, auth()->id());

        return $this->handleReservationResult($result, 'wish_reserved');
    }

    /**
     * Unreserve a wish.
     * @throws AuthorizationException
     */
    public function unreserve(int $wishId): RedirectResponse
    {
        $wish = $this->findWish($wishId);
        $this->authorize('unreserve', $wish);

        $result = $this->service->unreserveWishForUser($wish, auth()->id());

        return $this->handleReservationResult($result, 'wish_unreserved');
    }

    /**
     * Display user reservations.
     */
    public function index(): View
    {
        $reservations = $this->service->getUserReservations(auth()->id());
        $statistics = $this->service->getUserReservationStatistics(auth()->id());

        return view('reservations.index', compact('reservations', 'statistics'));
    }

    /**
     * Display wish list reservations.
     */
    public function wishList(int $wishListId): View
    {
        $reservations = $this->service->getWishListReservations($wishListId);
        $statistics = $this->service->getWishListReservationStatistics($wishListId);

        return view('reservations.wish-list', compact('reservations', 'statistics'));
    }

    /**
     * Find wish by ID with necessary relations.
     */
    private function findWish(int $wishId): Wish
    {
        return Wish::with(['wishList', 'reservation.user'])
            ->findOrFail($wishId);
    }

    /**
     * Handle reservation result.
     */
    private function handleReservationResult(bool|string $result, string $successMessageKey): RedirectResponse
    {
        return $result === true
            ? back()->with('success', __('messages.' . $successMessageKey))
            : back()->with('error', $result);
    }
}
