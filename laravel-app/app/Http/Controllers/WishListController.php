<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\WishList;
use App\Services\WishListService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class WishListController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected WishListService $service
    ) {}

    /**
     * Display user wish lists.
     */
    public function index(): View
    {
        $wishListDTO = $this->service->getIndexData(Auth::id());

        return view('wishlists.index', $wishListDTO->toArray());
    }

    /**
     * Display wish list creation form.
     */
    public function create(): View
    {
        return view('wishlists.create');
    }

    /**
     * Display the specified wish list.
     */
    public function show(WishList $wishList): View
    {
        $this->authorize('view', $wishList);

        return view('wishlists.show', compact('wishList'));
    }

    /**
     * Store new wish list.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $this->service->create($request->all(), Auth::id());

        } catch (Exception $e) {

            return $this->redirectToIndexWithError('error_creating_list', $e->getMessage());
        }

        return $this->redirectToIndex('wishlist_created');
    }

    /**
     * Display wish list edit form.
     * @throws AuthorizationException
     */
    public function edit(WishList $wishList): View
    {
        $this->authorize('update', $wishList);

        return view('wishlists.edit', compact('wishList'));
    }

    /**
     * Update wish list.
     * @throws AuthorizationException
     */
    public function update(Request $request, WishList $wishList): RedirectResponse
    {
        $this->authorize('update', $wishList);

        try {
            $this->service->update($wishList, $request->all());

        } catch (Exception $e) {

            return $this->redirectToIndexWithError('error_updating_list', $e->getMessage());
        }

        return $this->redirectToIndex('wishlist_updated');
    }

    /**
     * Display public wish list.
     */
    public function public(string $uuid): View
    {
        $publicWishListDTO = $this->service->getPublicWishListData($uuid);

        return view('wishlists.public', $publicWishListDTO->toArray());
    }

    /**
     * Delete wish list.
     * @throws AuthorizationException
     */
    public function destroy(WishList $wishList): RedirectResponse
    {
        $this->authorize('delete', $wishList);

        try {
            $this->service->delete($wishList);
        } catch (Exception $e) {
            return $this->redirectToIndexWithError('error_deleting_list', $e->getMessage());
        }

        return $this->redirectToIndex('wishlist_deleted');
    }

    /**
     * Redirect to index with success message.
     */
    private function redirectToIndex(string $messageKey): RedirectResponse
    {
        return redirect()->route('wish-lists.index')
            ->with('success', __('messages.' . $messageKey));
    }

    /**
     * Redirect to index with error message.
     */
    private function redirectToIndexWithError(string $messageKey, string $errorMessage): RedirectResponse
    {
        return redirect()->route('wish-lists.index')
            ->with('error', __('messages.' . $messageKey) . $errorMessage);
    }
}
