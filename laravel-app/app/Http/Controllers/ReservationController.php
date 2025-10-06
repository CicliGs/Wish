<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Wish;
use App\Models\WishList;
use App\Services\ReservationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ReservationService $service
    ) {}

    /**
     * Reserve a wish.
     *
     * @throws AuthorizationException
     */
    public function reserve(int $wishId): RedirectResponse
    {
        $wish = $this->findWish($wishId);
        $this->authorize('reserve', $wish);

        $this->service->reserve($wish, Auth::user());

        return back()->with('success', __('messages.wish_reserved'));
    }

    /**
     * Unreserve a wish.
     *
     * @throws AuthorizationException
     */
    public function unreserve(int $wishId): RedirectResponse
    {
        $wish = $this->findWish($wishId);
        $this->authorize('unreserve', $wish);

        $this->service->unreserve($wish, Auth::user());

        return back()->with('success', __('messages.wish_unreserved'));
    }

    /**
     * Display user reservations.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $reservations = $this->service->getReservations($user);
        $statistics = $this->service->getStatistics($user);

        return view('reservations.index', compact('reservations', 'statistics'));
    }

    /**
     * Display wish list reservations.
     */
    public function wishList(WishList $wishList): View
    {
        $reservations = $this->service->getReservations($wishList);
        $statistics = $this->service->getStatistics($wishList);

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
}
