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

class WishListController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected WishListService $service) {}

    /**
     * Отображает список списков желаний пользователя.
     */
    public function index(): View
    {
        $data = $this->service->getIndexData(Auth::id());
        
        return view('wishlists.index', $data);
    }

    /**
     * Отображает форму создания списка желаний.
     */
    public function create(): View
    {
        return view('wishlists.create');
    }

    /**
     * Сохраняет новый список желаний.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $this->service->create($request->all(), Auth::id());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', __('messages.error_creating_list') . $e->getMessage());
        }

        return redirect()->route('wish-lists.index')->with('success', __('messages.wishlist_created'));
    }

    /**
     * Отображает форму редактирования списка желаний.
     */
    public function edit(WishList $wishList): View
    {
        $this->authorize('update', $wishList);

        return view('wishlists.edit', compact('wishList'));
    }

    /**
     * Обновляет список желаний.
     */
    public function update(Request $request, WishList $wishList): RedirectResponse
    {
        $this->authorize('update', $wishList);

        try {
            $this->service->update($wishList, $request->all());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', __('messages.error_updating_list') . $e->getMessage());
        }

        return redirect()->route('wish-lists.index')->with('success', __('messages.wishlist_updated'));
    }

    /**
     * Отображает публичный список желаний.
     */
    public function public(string $publicId): View
    {
        $data = $this->service->getPublicWishListData($publicId);
        
        return view('wishlists.public', $data);
    }

    /**
     * Удаляет список желаний.
     */
    public function destroy(WishList $wishList): RedirectResponse
    {
        $this->authorize('delete', $wishList);

        try {
            $this->service->delete($wishList);
        } catch (\Exception $e) {
            return back()->with('error', __('messages.error_deleting_list') . $e->getMessage());
        }

        return redirect()->route('wish-lists.index')->with('success', __('messages.wishlist_deleted'));
    }
}
