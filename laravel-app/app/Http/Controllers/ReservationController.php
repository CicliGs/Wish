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

    public function reserve(int $wishId): RedirectResponse
    {
        $wish = Wish::findOrFail($wishId);
        $result = $this->service->reserve($wish, Auth::id());

        if ($result === true) {
            return back()->with('success', 'Подарок забронирован!');
        }

        return back()->with('error', $result);
    }

    public function unreserve(int $wishId): RedirectResponse
    {
        $wish = Wish::findOrFail($wishId);
        $result = $this->service->unreserve($wish, Auth::id());

        if ($result === true) {
            return back()->with('success', __('Бронирование снято!'));
        }

        return back()->with('error', $result);
    }

    public function index(): View
    {
        $reservations = $this->service->getUserReservations(Auth::id());
        $statistics = $this->service->getUserReservationStatistics(Auth::id());

        return view('reservations.index', compact('reservations', 'statistics'));
    }

    public function wishList(int $wishListId): View
    {
        $reservations = $this->service->getWishListReservations($wishListId);
        $statistics = $this->service->getWishListReservationStatistics($wishListId);

        return view('reservations.wish-list', compact('reservations', 'statistics'));
    }
}
