<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWishRequest;
use App\Http\Requests\UpdateWishRequest;
use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use App\Services\WishService;
use App\Services\ReservationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class WishController extends Controller
{
    public function __construct(
        private readonly WishService $wishService,
        private readonly ReservationService $reservationService
    ) {}

    public function index(WishList $wishList): View
    {
        $wishDTO = $this->wishService->getIndexData($wishList, Auth::user());

        return view('wishes.index', $wishDTO->toArray());
    }

    public function create(WishList $wishList): View
    {
        return view('wishes.create', compact('wishList'));
    }

    public function store(StoreWishRequest $request, WishList $wishList): RedirectResponse
    {
        $imageFile = $request->hasFile('image_file') ? $request->file('image_file') : null;
        $this->wishService->create($request->getWishData(), $wishList, Auth::user(), $imageFile);

        return redirect()
            ->route('wishes.index', $wishList)
            ->with('success', __('messages.wish_created'));
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(WishList $wishList, Wish $wish): View
    {
        $this->authorize('update', $wish);

        return view('wishes.edit', compact('wish', 'wishList'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(UpdateWishRequest $request, WishList $wishList, Wish $wish): RedirectResponse
    {
        $this->authorize('update', $wish);

        $this->wishService->update($wish, $request->getWishData(), Auth::user());

        return redirect()
            ->route('wishes.index', $wishList)
            ->with('success', __('messages.wish_updated'));
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(WishList $wishList, Wish $wish): RedirectResponse
    {
        $this->authorize('delete', $wish);

        $this->wishService->delete($wish, Auth::user());

        return redirect()
            ->route('wishes.index', $wishList)
            ->with('success', __('messages.wish_deleted'));
    }

    public function available(WishList $wishList): View
    {
        $wishDTO = $this->wishService->getData($wishList, Auth::user(), 'available');

        return view('wishes.available', $wishDTO->toArray());
    }

    public function reserved(WishList $wishList): View
    {
        $wishDTO = $this->wishService->getData($wishList, Auth::user(), 'reserved');

        return view('wishes.reserved', $wishDTO->toArray());
    }

    public function showUser(User $user): View
    {
        $wishDTO = $this->wishService->getWishListsData($user);

        return view('wishes.user_all', $wishDTO->toArray());
    }

    public function showUserWishList(User $user, WishList $wishList): View
    {
        $wishDTO = $this->wishService->getWishListData($user, $wishList);
        $data = $wishDTO->toArray();
        $data['isGuest'] = !auth()->check();

        return view('wishes.user', $data);
    }

    /**
     * @throws AuthorizationException
     */
    public function unreserve(Wish $wish): RedirectResponse
    {
        $this->authorize('unreserve', $wish);

        try {
            $this->reservationService->unreserve($wish, Auth::user());

            return back()->with('success', __('messages.wish_unreserved'));
        } catch (RuntimeException $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function reserve(Wish $wish): RedirectResponse
    {
        $this->authorize('reserve', $wish);

        try {
            $this->reservationService->reserve($wish, Auth::user());

            return back()->with('success', __('messages.wish_reserved'));
        } catch (RuntimeException $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function unreserveAjax(Wish $wish): JsonResponse
    {
        $this->authorize('unreserve', $wish);

        try {
            $this->reservationService->unreserve($wish, Auth::user());

            return response()->json(['success' => __('messages.wish_unreserved')]);
        } catch (RuntimeException $e) {

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function reserveAjax(Wish $wish): JsonResponse
    {
        $this->authorize('reserve', $wish);

        try {
            $this->reservationService->reserve($wish, Auth::user());

            return response()->json(['success' => __('messages.wish_reserved')]);
        } catch (RuntimeException $e) {

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
