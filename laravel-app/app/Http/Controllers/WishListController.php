<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\WishList;
use App\Services\WishListService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class WishListController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected WishListService $service) {}

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
     * Store new wish list.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $this->service->create($request->all(), Auth::id());
            return $this->redirectToIndex('wishlist_created');
        } catch (Exception $e) {
            return $this->handleError($e, 'error_creating_list');
        }
    }

    /**
     * Display wish list edit form.
     */
    public function edit(WishList $wishList): View
    {
        $this->authorize('update', $wishList);

        return view('wishlists.edit', compact('wishList'));
    }

    /**
     * Update wish list.
     */
    public function update(Request $request, WishList $wishList): RedirectResponse
    {
        $this->authorize('update', $wishList);

        try {
            $this->service->update($wishList, $request->all());
            return $this->redirectToIndex('wishlist_updated');
        } catch (Exception $e) {
            return $this->handleError($e, 'error_updating_list');
        }
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
     */
    public function destroy(WishList $wishList): RedirectResponse
    {
        $this->authorize('delete', $wishList);

        try {
            $this->service->delete($wishList);
            return $this->redirectToIndex('wishlist_deleted');
        } catch (Exception $e) {
            return $this->handleError($e, 'error_deleting_list');
        }
    }

    /**
     * Regenerate UUID for wish list.
     */
    public function regenerateUuid(WishList $wishList): RedirectResponse
    {
        $this->authorize('update', $wishList);

        try {
            $this->service->regenerateUuid($wishList);
            return back()->with('success', __('messages.uuid_regenerated'));
        } catch (Exception $e) {
            return back()->with('error', __('messages.error_regenerating_uuid') . $e->getMessage());
        }
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
     * Handle error and return back with error message.
     */
    private function handleError(Exception $e, string $messageKey): RedirectResponse
    {
        return back()->withInput()
            ->with('error', __('messages.' . $messageKey) . $e->getMessage());
    }
}
