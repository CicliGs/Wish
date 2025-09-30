<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWishRequest;
use App\Http\Requests\UpdateWishRequest;
use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use App\Services\WishService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class WishController extends Controller
{
    public function __construct(
        private readonly WishService $wishService
    ) {}

    public function index(WishList $wishList): View
    {
        $wishDTO = $this->wishService->getIndexData($wishList->id, auth()->id());

        return view('wishes.index', $wishDTO->toArray());
    }

    public function create(WishList $wishList): View
    {
        return view('wishes.create', compact('wishList'));
    }

    public function store(StoreWishRequest $request, WishList $wishList): RedirectResponse
    {
        try {
            $imageFile = $request->hasFile('image_file') ? $request->file('image_file') : null;
            $this->wishService->createWishWithImage($request->getWishData(), $wishList->id, $imageFile);

            return redirect()
                ->route('wishes.index', $wishList)
                ->with('success', __('messages.wish_created'));
        } catch (Exception $e) {

            return back()
                ->with('error', __('messages.error_creating_wish') . ': ' . $e->getMessage());
        }
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

        try {
            $this->wishService->updateWish($wish, $request->getWishData());

            return redirect()
                ->route('wishes.index', $wishList)
                ->with('success', __('messages.wish_updated'));
        } catch (Exception $e) {

            return back()
                ->with('error', __('messages.error_updating_wish') . ': ' . $e->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(WishList $wishList, Wish $wish): RedirectResponse
    {
        $this->authorize('delete', $wish);

        try {
            $this->wishService->deleteWish($wish);

            return redirect()
                ->route('wishes.index', $wishList)
                ->with('success', __('messages.wish_deleted'));
        } catch (Exception $e) {

            return back()
                ->with('error', __('messages.error_deleting_wish') . ': ' . $e->getMessage());
        }
    }

    public function available(WishList $wishList): View
    {
        $wishDTO = $this->wishService->getAvailableData($wishList->id, auth()->id());

        return view('wishes.available', $wishDTO->toArray());
    }

    public function reserved(WishList $wishList): View
    {
        $wishDTO = $this->wishService->getReservedData($wishList->id, auth()->id());

        return view('wishes.reserved', $wishDTO->toArray());
    }

    /**
     * @throws AuthorizationException
     */
    public function unreserve(Wish $wish): RedirectResponse
    {
        $this->authorize('unreserve', $wish);

        $success = $this->wishService->unreserveWish($wish, auth()->id());
        $message = $success ? 'wish_unreserved' : 'cannot_unreserve_wish';
        $type = $success ? 'success' : 'error';

        return back()->with($type, __("messages.$message"));
    }

    public function reserve(Wish $wish): RedirectResponse
    {
        $this->authorize('reserve', $wish);

        $success = $this->wishService->reserveWish($wish, auth()->id());
        $message = $success ? 'wish_reserved' : 'cannot_reserve_wish';
        $type = $success ? 'success' : 'error';

        return back()->with($type, __("messages.$message"));
    }

    public function showUser(User $user): View
    {
        $wishDTO = $this->wishService->getUserWishListsData($user->id);

        return view('wishes.user_all', $wishDTO->toArray());
    }

    public function showUserWishList(User $user, WishList $wishList): View
    {
        $wishDTO = $this->wishService->getUserWishListData($user->id, $wishList->id);
        $data = $wishDTO->toArray();
        $data['isGuest'] = !auth()->check();

        return view('wishes.user', $data);
    }

    /**
     * @throws AuthorizationException
     */
    public function unreserveAjax(Wish $wish): JsonResponse
    {
        $this->authorize('unreserve', $wish);

        $success = $this->wishService->unreserveWish($wish, auth()->id());

        return $success
            ? response()->json(['success' => __('messages.wish_unreserved')])
            : response()->json(['error' => __('messages.cannot_unreserve_wish')], 400);
    }

    /**
     * @throws AuthorizationException
     */
    public function reserveAjax(Wish $wish): JsonResponse
    {
        $this->authorize('reserve', $wish);

        $success = $this->wishService->reserveWish($wish, auth()->id());

        return $success
            ? response()->json(['success' => __('messages.wish_reserved')])
            : response()->json(['error' => __('messages.cannot_reserve_wish')], 400);
    }
}
