<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Wish;
use App\Services\ReservationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function __construct(protected ReservationService $service) {}

    /**
     * Reserve a wish.
     */
    public function reserve(int $wishId): RedirectResponse
    {
        $wish = $this->findWish($wishId);
        $result = $this->service->reserve($wish, Auth::id());

        return $this->handleReservationResult($result, 'wish_reserved');
    }

    /**
     * Unreserve a wish.
     */
    public function unreserve(int $wishId): RedirectResponse
    {
        $wish = $this->findWish($wishId);
        $result = $this->service->unreserve($wish, Auth::id());

        return $this->handleReservationResult($result, 'wish_unreserved');
    }

    /**
     * Display user reservations.
     */
    public function index(): View
    {
        $reservations = $this->service->getUserReservations(Auth::id());
        $statistics = $this->service->getUserReservationStatistics(Auth::id());

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
     * Find wish by ID.
     */
    private function findWish(int $wishId): Wish
    {
        return Wish::findOrFail($wishId);
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
