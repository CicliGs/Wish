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

class WishController extends Controller
{
    private const HTTP_BAD_REQUEST = 400;

    public function __construct(
        protected WishService $service
    ) {
        $this->middleware('auth');
    }

    /**
     * Display wishes in a wish list.
     */
    public function index(WishList $wishList): View
    {
        $wishListId = $wishList->id;
        $wishDTO = $this->service->getIndexData($wishListId, auth()->id());

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
     * Store new wish.
     */
    public function store(StoreWishRequest $request, WishList $wishList): RedirectResponse
    {
        try {
            $this->createWish($request, $wishList);
        } catch (Exception $e) {
            return $this->handleError($e, 'error_creating_wish');
        }

        $wishListId = $wishList->id;

        return $this->redirectToWishList($wishListId, 'wish_created');
    }

    /**
     * Display wish edit form.
     * @throws AuthorizationException
     */
    public function edit(WishList $wishList, Wish $wish): View
    {
        $this->authorize('update', $wish);

        return view('wishes.edit', compact('wish', 'wishList'));
    }

    /**
     * Update wish.
     * @throws AuthorizationException
     */
    public function update(UpdateWishRequest $request, WishList $wishList, Wish $wish): RedirectResponse
    {
        $this->authorize('update', $wish);

        try {
            $this->service->update($wish, $request);

        } catch (Exception $e) {

            return $this->handleError($e, 'error_updating_wish');
        }

        $wishListId = $wishList->id;
        return $this->redirectToWishList($wishListId, 'wish_updated');
    }

    /**
     * Delete wish.
     * @throws AuthorizationException
     */
    public function destroy(WishList $wishList, Wish $wish): RedirectResponse
    {
        $this->authorize('delete', $wish);

        try {
            $this->service->delete($wish);

        } catch (Exception $e) {

            return $this->handleError($e, 'error_deleting_wish');
        }

        $wishListId = $wishList->id;

        return $this->redirectToWishList($wishListId, 'wish_deleted');
    }

    /**
     * Display available wishes.
     */
    public function available(WishList $wishList): View
    {
        $wishListId = $wishList->id;
        $wishDTO = $this->service->getAvailableData($wishListId, auth()->id());

        return view('wishes.available', $wishDTO->toArray());
    }

    /**
     * Display reserved wishes.
     */
    public function reserved(WishList $wishList): View
    {
        $wishListId = $wishList->id;
        $wishDTO = $this->service->getReservedData($wishListId, auth()->id());

        return view('wishes.reserved', $wishDTO->toArray());
    }

    /**
     * Unreserve wish.
     * @throws AuthorizationException
     */
    public function unreserve(Wish $wish): RedirectResponse
    {
        $this->authorize('unreserve', $wish);

        if (!$this->service->unreserveWish($wish, auth()->id())) {
            return back()->with('error', __('messages.cannot_unreserve_wish'));
        }

        return back()->with('success', __('messages.wish_unreserved'));
    }

    /**
     * Reserve wish.
     * @throws AuthorizationException
     */
    public function reserve(Wish $wish): RedirectResponse
    {
        $this->authorize('reserve', $wish);

        if (!$this->service->reserveWish($wish, auth()->id())) {

            return back()->with('error', __('messages.cannot_reserve_wish'));
        }

        return back()->with('success', __('messages.wish_reserved'));
    }

    /**
     * Display user wishes.
     */
    public function showUser(User $user): View
    {
        $userId = $user->id;
        $wishDTO = $this->service->getUserWishListsData($userId);

        return view('wishes.user_all', $wishDTO->toArray());
    }

    /**
     * Display user wish list.
     */
    public function showUserWishList(User $user, WishList $wishList): View
    {
        $userId = $user->id;
        $wishListId = $wishList->id;
        $wishDTO = $this->service->getUserWishListData($userId, $wishListId);

        return view('wishes.user', $wishDTO->toArray());
    }

    /**
     * Unreserve wish via AJAX.
     * @throws AuthorizationException
     */
    public function unreserveAjax(Wish $wish): JsonResponse
    {
        $this->authorize('unreserve', $wish);

        if (!$this->service->unreserveWish($wish, auth()->id())) {
            return $this->createErrorResponse('cannot_unreserve_wish');
        }

        return $this->createSuccessResponse('wish_unreserved');
    }

    /**
     * Reserve wish via AJAX.
     * @throws AuthorizationException
     */
    public function reserveAjax(Wish $wish): JsonResponse
    {
        $this->authorize('reserve', $wish);

        if (!$this->service->reserveWish($wish, auth()->id())) {
            return $this->createErrorResponse('cannot_reserve_wish');
        }

        return $this->createSuccessResponse('wish_reserved');
    }

    /**
     * Create wish with image handling.
     */
    private function createWish(StoreWishRequest $request, WishList $wishList): void
    {
        $imageFile = $request->hasFile('image_file') ? $request->file('image_file') : null;
        $wishListId = $wishList->id;
        $this->service->createWithImage($request, $wishListId, $imageFile);
    }

    /**
     * Redirect to wish list with success message.
     */
    private function redirectToWishList(int $wishListId, string $message): RedirectResponse
    {
        return redirect()->route('wishes.index', $wishListId)->with('success', __("messages.$message"));
    }

    /**
     * Handle errors.
     */
    private function handleError(Exception $e, string $message): RedirectResponse
    {
        return back()->with('error', __("messages.$message") . ': ' . $e->getMessage());
    }



    /**
     * Create error response.
     */
    private function createErrorResponse(string $messageKey): JsonResponse
    {
        return response()->json(['error' => __('messages.' . $messageKey)], self::HTTP_BAD_REQUEST);
    }

    /**
     * Create success response.
     */
    private function createSuccessResponse(string $messageKey): JsonResponse
    {
        return response()->json(['success' => __('messages.' . $messageKey)]);
    }
}
