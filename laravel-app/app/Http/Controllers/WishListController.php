<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateWishListRequest;
use App\Http\Requests\UpdateWishListRequest;
use App\Models\WishList;
use App\Services\WishListService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

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
    public function store(CreateWishListRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), Auth::id());

        return redirect()->route('wish-lists.index')
            ->with('success', __('messages.wishlist_created'));
    }

    /**
     * Display wish list edit form.
     *
     * @throws AuthorizationException
     */
    public function edit(WishList $wishList): View
    {
        $this->authorize('update', $wishList);

        return view('wishlists.edit', compact('wishList'));
    }

    /**
     * Update wish list.
     *
     * @throws AuthorizationException
     */
    public function update(UpdateWishListRequest $request, WishList $wishList): RedirectResponse
    {
        $this->authorize('update', $wishList);

        $this->service->update($wishList, $request->validated());

        return redirect()->route('wish-lists.index')
            ->with('success', __('messages.wishlist_updated'));
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
     *
     * @throws AuthorizationException
     */
    public function destroy(WishList $wishList): RedirectResponse
    {
        $this->authorize('delete', $wishList);

        $this->service->delete($wishList);

        return redirect()->route('wish-lists.index')
            ->with('success', __('messages.wishlist_deleted'));
    }
}
