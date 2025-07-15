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

    public function index(): View
    {
        $wishLists = $this->service->findByUser(Auth::id());
        $statistics = $this->service->getStatistics(Auth::id());

        return view('wishlists.index', compact('wishLists', 'statistics'));
    }

    public function create(): View
    {
        return view('wishlists.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->service->create($request->all(), Auth::id());

            return redirect()->route('wish-lists.index')->with('success', 'Список желаний создан!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Ошибка при создании списка: '.$e->getMessage());
        }
    }

    public function edit(WishList $wishList): View
    {
        $this->authorize('update', $wishList);

        return view('wishlists.edit', compact('wishList'));
    }

    public function update(Request $request, WishList $wishList): RedirectResponse
    {
        $this->authorize('update', $wishList);

        try {
            $this->service->update($wishList, $request->all());
        } catch (\Exception $e) {

            return back()->withInput()->with('error', 'Ошибка при обновлении: '.$e->getMessage());
        }

        return redirect()->route('wish-lists.index')->with('success', 'Список желаний обновлен!');
    }

    public function public(string $publicId): View
    {
        $wishList = $this->service->findPublic($publicId);

        if (! $wishList) {
            abort(404, 'Список желаний не найден');
        }

        $wishes = $wishList->wishes;

        return view('wishlists.public', compact('wishList', 'wishes'));
    }
    //
    //    public function regeneratePublicId(WishList $wishList): RedirectResponse
    //    {
    //        $this->authorize('update', $wishList);
    //
    //        try {
    //            $this->service->regeneratePublicId($wishList);
    //
    //            return back()->with('success', 'Публичная ссылка обновлена!');
    //        } catch (\Exception $e) {
    //            return back()->with('error', 'Ошибка при обновлении ссылки: '.$e->getMessage());
    //        }
    //    }

    public function destroy(WishList $wishList): RedirectResponse
    {
        $this->authorize('delete', $wishList);

        try {
            $this->service->delete($wishList);
        } catch (\Exception $e) {

            return back()->with('error', 'Ошибка при удалении: '.$e->getMessage());
        }

        return redirect()->route('wish-lists.index')->with('success', 'Список желаний удален!');
    }
}
