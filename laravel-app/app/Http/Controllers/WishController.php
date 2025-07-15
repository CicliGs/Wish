<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWishRequest;
use App\Http\Requests\UpdateWishRequest;
use App\Models\Wish;
use App\Models\WishList;
use App\Services\WishService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class WishController extends Controller
{
    public function __construct(protected WishService $service) {}

    public function index(int $wishListId): View
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $wishes = $this->service->findByWishList($wishList->id);
        $statistics = $this->service->getWishListStatistics($wishList->id);

        return view('wishes.index', compact('wishes', 'wishList', 'statistics'));
    }

    public function create(int $wishListId): View
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);

        return view('wishes.create', compact('wishList'));
    }

    public function store(StoreWishRequest $request, int $wishListId): RedirectResponse
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);

        try {
            $this->service->create($request->validated(), $wishList->id);

            return redirect()->route('wishes.index', $wishList->id)
                ->with('success', 'Желание добавлено!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Ошибка при создании: '.$e->getMessage());
        }
    }

    public function edit(int $wishListId, Wish $wish): View
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $this->authorize('update', $wish);

        return view('wishes.edit', compact('wish', 'wishList'));
    }

    public function update(UpdateWishRequest $request, int $wishListId, Wish $wish): RedirectResponse
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $this->authorize('update', $wish);

        try {
            $this->service->update($wish, $request->validated());

            return redirect()->route('wishes.index', $wishList->id)
                ->with('success', 'Желание обновлено!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Ошибка при обновлении: '.$e->getMessage());
        }
    }

    public function destroy(int $wishListId, Wish $wish): RedirectResponse
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $this->authorize('delete', $wish);

        try {
            $this->service->delete($wish);

            return redirect()->route('wishes.index', $wishList->id)
                ->with('success', 'Желание удалено!');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при удалении: '.$e->getMessage());
        }
    }

    public function available(int $wishListId): View
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $wishes = $this->service->getAvailableWishes($wishList->id);

        return view('wishes.available', compact('wishes', 'wishList'));
    }

    public function reserved(int $wishListId): View
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $wishes = $this->service->getReservedWishes($wishList->id);

        return view('wishes.reserved', compact('wishes', 'wishList'));
    }
}
