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
use Illuminate\Support\Facades\Auth;

class WishController extends Controller
{
    public function __construct(protected WishService $service) {}

    /**
     * Отображает список желаний в списке желаний.
     */
    public function index(int $wishListId): View
    {
        $data = $this->service->getIndexData($wishListId, Auth::id());
        
        return view('wishes.index', $data);
    }

    /**
     * Отображает форму создания желания.
     */
    public function create(int $wishListId): View
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);

        return view('wishes.create', compact('wishList'));
    }

    /**
     * Сохраняет новое желание.
     */
    public function store(StoreWishRequest $request, int $wishListId): RedirectResponse
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);

        try {
            $data = $request->validated();
            $imageFile = $request->hasFile('image_file') ? $request->file('image_file') : null;
            $this->service->createWithImage($data, $wishList->id, $imageFile);

        } catch (\Exception $e) {
            return back()->withInput()->with('error', __('messages.error_creating_wish') . $e->getMessage());
        }

        return redirect()->route('wishes.index', $wishList->id)
            ->with('success', __('messages.wish_created'));
    }

    /**
     * Отображает форму редактирования желания.
     */
    public function edit(int $wishListId, Wish $wish): View
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $this->authorize('update', $wish);

        return view('wishes.edit', compact('wish', 'wishList'));
    }

    /**
     * Обновляет желание.
     */
    public function update(UpdateWishRequest $request, int $wishListId, Wish $wish): RedirectResponse
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $this->authorize('update', $wish);

        try {
            $this->service->update($wish, $request->validated());

        } catch (\Exception $e) {
            return back()->withInput()->with('error', __('messages.error_updating_wish') . $e->getMessage());
        }
        
        return redirect()->route('wishes.index', $wishList->id)
            ->with('success', __('messages.wish_updated'));
    }

    /**
     * Удаляет желание.
     */
    public function destroy(int $wishListId, Wish $wish): RedirectResponse
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $this->authorize('delete', $wish);

        try {
            $this->service->delete($wish);

        } catch (\Exception $e) {
            return back()->with('error', __('messages.error_deleting_wish') . $e->getMessage());
        }

        return redirect()->route('wishes.index', $wishList->id)
            ->with('success', __('messages.wish_deleted'));
    }

    /**
     * Отображает доступные желания.
     */
    public function available(int $wishListId): View
    {
        $data = $this->service->getAvailableData($wishListId, Auth::id());
        
        return view('wishes.available', $data);
    }

    /**
     * Отображает зарезервированные желания.
     */
    public function reserved(int $wishListId): View
    {
        $data = $this->service->getReservedData($wishListId, Auth::id());
        
        return view('wishes.reserved', $data);
    }

    /**
     * Отменяет резервирование желания.
     */
    public function unreserve(int $wishListId, Wish $wish): RedirectResponse
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $this->authorize('reserve', $wish);

        try {
            $this->service->unreserve($wish, Auth::id());

        } catch (\Exception $e) {
            return back()->with('error', __('messages.error_unreserving_wish') . $e->getMessage());
        }

        return redirect()->route('wishes.index', $wishList->id)
            ->with('success', __('messages.wish_unreserved'));
    }

    /**
     * Резервирует желание.
     */
    public function reserve(int $wishListId, Wish $wish): RedirectResponse
    {
        $wishList = WishList::forUser(Auth::id())->findOrFail($wishListId);
        $this->authorize('reserve', $wish);

        try {
            $this->service->reserve($wish, Auth::id());

        } catch (\Exception $e) {
            return back()->with('error', __('messages.error_reserving_wish') . $e->getMessage());
        }

        return redirect()->route('wishes.index', $wishList->id)
            ->with('success', __('messages.wish_reserved'));
    }

    /**
     * Отображает все желания пользователя.
     */
    public function showUser(int $userId): View
    {
        $data = $this->service->getUserWishesData($userId);
        
        return view('wishes.user_all', $data);
    }

    /**
     * Отображает список желаний пользователя.
     */
    public function showUserWishList(int $userId, int $wishListId): View
    {
        $data = $this->service->getUserWishListData($userId, $wishListId);
        
        return view('wishes.user', $data);
    }
}
