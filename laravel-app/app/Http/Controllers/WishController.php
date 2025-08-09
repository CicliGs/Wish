<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWishRequest;
use App\Http\Requests\UpdateWishRequest;
use App\Models\Wish;
use App\Models\WishList;
use App\Models\User;
use App\Services\WishService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class WishController extends Controller
{
    private const HTTP_UNAUTHORIZED = 401;
    private const HTTP_BAD_REQUEST = 400;

    public function __construct(protected WishService $service) {}

    /**
     * Display wishes in a wish list.
     */
    public function index(int $wishListId): View
    {
        $wishDTO = $this->service->getIndexData($wishListId, Auth::id());
        
        return view('wishes.index', $wishDTO->toArray());
    }

    /**
     * Display wish creation form.
     */
    public function create(int $wishListId): View
    {
        $wishList = $this->findWishListForUser($wishListId);

        return view('wishes.create', compact('wishList'));
    }

    /**
     * Store new wish.
     */
    public function store(StoreWishRequest $request, int $wishListId): RedirectResponse
    {
        $wishList = $this->findWishListForUser($wishListId);

        try {
            $this->createWish($request, $wishList);
            return $this->redirectToWishList($wishList->id, 'wish_created');
        } catch (Exception $e) {
            return $this->handleError($e, 'error_creating_wish');
        }
    }

    /**
     * Display wish edit form.
     */
    public function edit(int $wishListId, Wish $wish): View
    {
        $wishList = $this->findWishListForUser($wishListId);
        $this->authorize('update', $wish);

        return view('wishes.edit', compact('wish', 'wishList'));
    }

    /**
     * Update wish.
     */
    public function update(UpdateWishRequest $request, int $wishListId, Wish $wish): RedirectResponse
    {
        $wishList = $this->findWishListForUser($wishListId);
        $this->authorize('update', $wish);

        try {
            $this->service->update($wish, $request->validated());
            return $this->redirectToWishList($wishList->id, 'wish_updated');
        } catch (Exception $e) {
            return $this->handleError($e, 'error_updating_wish');
        }
    }

    /**
     * Delete wish.
     */
    public function destroy(int $wishListId, Wish $wish): RedirectResponse
    {
        $wishList = $this->findWishListForUser($wishListId);
        $this->authorize('delete', $wish);

        try {
            $this->service->delete($wish);
            return $this->redirectToWishList($wishList->id, 'wish_deleted');
        } catch (Exception $e) {
            return $this->handleError($e, 'error_deleting_wish');
        }
    }

    /**
     * Display available wishes.
     */
    public function available(int $wishListId): View
    {
        $wishDTO = $this->service->getAvailableData($wishListId, Auth::id());
        
        return view('wishes.available', $wishDTO->toArray());
    }

    /**
     * Display reserved wishes.
     */
    public function reserved(int $wishListId): View
    {
        $wishDTO = $this->service->getReservedData($wishListId, Auth::id());
        
        return view('wishes.reserved', $wishDTO->toArray());
    }

    /**
     * Unreserve wish.
     */
    public function unreserve(WishList $wishList, Wish $wish): RedirectResponse
    {
        $this->authorize('unreserve', $wish);

        if (!$this->service->unreserveWish($wish, Auth::id())) {
            return back()->with('error', __('messages.cannot_unreserve_wish'));
        }

        return back()->with('success', __('messages.wish_unreserved'));
    }

    /**
     * Reserve wish.
     */
    public function reserve(WishList $wishList, Wish $wish): RedirectResponse
    {
        $this->authorize('reserve', $wish);

        if (!$this->service->reserveWish($wish, Auth::id())) {
            return back()->with('error', __('messages.cannot_reserve_wish'));
        }

        return back()->with('success', __('messages.wish_reserved'));
    }

    /**
     * Display user wishes.
     */
    public function showUser(int $userId): View
    {
        $user = $this->findUser($userId);
        $wishDTO = $this->service->getUserWishListsData($userId);
        
        return view('wishes.user_all', $wishDTO->toArray());
    }

    /**
     * Display user wish list.
     */
    public function showUserWishList(int $userId, int $wishListId): View
    {
        $user = $this->findUser($userId);
        $wishDTO = $this->service->getUserWishListData($userId, $wishListId);
        
        return view('wishes.user', $wishDTO->toArray());
    }

    /**
     * Unreserve wish via AJAX.
     */
    public function unreserveAjax(Wish $wish): JsonResponse
    {
        if (!$this->isUserAuthenticated()) {
            return $this->createUnauthorizedResponse();
        }

        $this->authorize('unreserve', $wish);

        if (!$this->service->unreserveWish($wish, Auth::id())) {
            return $this->createErrorResponse('cannot_unreserve_wish');
        }

        return $this->createSuccessResponse('wish_unreserved');
    }

    /**
     * Reserve wish via AJAX.
     */
    public function reserveAjax(Wish $wish): JsonResponse
    {
        if (!$this->isUserAuthenticated()) {
            return $this->createUnauthorizedResponse();
        }

        $this->authorize('reserve', $wish);

        if (!$this->service->reserveWish($wish, Auth::id())) {
            return $this->createErrorResponse('cannot_reserve_wish');
        }

        return $this->createSuccessResponse('wish_reserved');
    }

    /**
     * Find wish list for authenticated user.
     */
    private function findWishListForUser(int $wishListId): WishList
    {
        return WishList::forUser(Auth::id())->findOrFail($wishListId);
    }

    /**
     * Find user by ID.
     */
    private function findUser(int $userId): User
    {
        return User::findOrFail($userId);
    }

    /**
     * Create wish with image handling.
     */
    private function createWish(StoreWishRequest $request, WishList $wishList): void
    {
        $data = $request->validated();
        $imageFile = $this->getImageFile($request);
        $this->service->createWithImage($data, $wishList->id, $imageFile);
    }

    /**
     * Get image file from request if present.
     */
    private function getImageFile(StoreWishRequest $request): ?object
    {
        return $request->hasFile('image_file') ? $request->file('image_file') : null;
    }

    /**
     * Check if user is authenticated.
     */
    private function isUserAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Create unauthorized response.
     */
    private function createUnauthorizedResponse(): JsonResponse
    {
        return response()->json(['error' => __('messages.unauthorized')], self::HTTP_UNAUTHORIZED);
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

    /**
     * Redirect to wish list with success message.
     */
    private function redirectToWishList(int $wishListId, string $messageKey): RedirectResponse
    {
        return redirect()->route('wishes.index', $wishListId)
            ->with('success', __('messages.' . $messageKey));
    }

    /**
     * Handle error and return back with error message.
     */
    private function handleError(Exception $e, string $messageKey): RedirectResponse
    {
        return back()->withInput()->with('error', __('messages.' . $messageKey) . $e->getMessage());
    }
}
