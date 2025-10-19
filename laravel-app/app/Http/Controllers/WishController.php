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

class WishController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly WishService $wishService,
        private readonly ReservationService $reservationService
    ) {}

    /**
     * Display wishes index page.
     */
    public function index(WishList $wishList): View
    {
        $wishDTO = $this->wishService->getIndexData($wishList, Auth::user());

        return view('wishes.index', $wishDTO->toArray());
    }

    /**
     * Display wish creation form.
     */
    public function create(WishList $wishList): View
    {
        return view('wishes.create', compact('wishList'));
    }

    /**
     * Store a new wish.
     */
    public function store(StoreWishRequest $request, WishList $wishList): RedirectResponse
    {
        $imageFile = $request->hasFile('image_file') ? $request->file('image_file') : null;
        $this->wishService->create($request->getWishData(), $wishList, Auth::user(), $imageFile);

        return redirect()
            ->route('wishes.index', $wishList)
            ->with('success', __('messages.wish_created'));
    }

    /**
     * Display wish edit form.
     *
     * @throws AuthorizationException
     */
    public function edit(WishList $wishList, Wish $wish): View
    {
        $this->authorize('update', $wish);

        return view('wishes.edit', compact('wish', 'wishList'));
    }

    /**
     * Update an existing wish.
     *
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
     * Delete a wish.
     *
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

    /**
     * Display available wishes.
     */
    public function available(WishList $wishList): View
    {
        $wishDTO = $this->wishService->getData($wishList, Auth::user(), 'available');

        return view('wishes.available', $wishDTO->toArray());
    }

    /**
     * Display reserved wishes.
     */
    public function reserved(WishList $wishList): View
    {
        $wishDTO = $this->wishService->getData($wishList, Auth::user(), 'reserved');

        return view('wishes.reserved', $wishDTO->toArray());
    }

    /**
     * Display user's wish lists.
     */
    public function showUser(User $user): View
    {
        $wishDTO = $this->wishService->getWishListsData($user);

        return view('wishes.user_all', $wishDTO->toArray());
    }

    /**
     * Display specific user wish list.
     */
    public function showUserWishList(User $user, WishList $wishList): View
    {
        $wishDTO = $this->wishService->getWishListData($user, $wishList);
        $data = $wishDTO->toArray();
        $data['isGuest'] = !auth()->check();

        return view('wishes.user', $data);
    }

    /**
     * Unreserve a wish.
     *
     * @throws AuthorizationException
     */
    public function unreserve(WishList $wishList, Wish $wish): RedirectResponse|JsonResponse
    {
        $this->authorize('unreserve', $wish);

        $this->reservationService->unreserve($wish, Auth::user());

        $message = __('messages.wish_unreserved');

        if (request()->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Reserve a wish.
     *
     * @throws AuthorizationException
     */
    public function reserve(WishList $wishList, Wish $wish): RedirectResponse|JsonResponse
    {
        $this->authorize('reserve', $wish);

        $this->reservationService->reserve($wish, Auth::user());

        $message = __('messages.wish_reserved');

        if (request()->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }
}
