<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Wish;
use App\Models\WishList;
use App\Services\ReservationService;
use App\Repositories\Contracts\WishRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Auth\Guard;

final class ReservationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ReservationService $service,
        protected WishRepositoryInterface $wishRepository,
        private readonly Guard $auth
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

        $this->service->reserve($wish, $this->auth->user());

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

        $this->service->unreserve($wish, $this->auth->user());

        return back()->with('success', __('messages.wish_unreserved'));
    }

    /**
     * Display user reservations.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = $this->auth->user();
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
        $wish = $this->wishRepository->findById($wishId);

        if (!$wish || !($wish instanceof Wish)) {
            abort(404, 'Wish not found');
        }

        return $wish;
    }
}
