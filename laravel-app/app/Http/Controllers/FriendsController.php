<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FriendService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FriendsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display friends page.
     */
    public function index(FriendService $friendService): View
    {
        /** @var User $user */
        $user = auth()->user();
        $selectedFriendId = $this->getSelectedFriendId();
        $friendsDTO = $friendService->getFriendsPageData($user, $selectedFriendId);

        return view('friends.index', $friendsDTO->toArray());
    }

    /**
     * Search friends.
     */
    public function search(Request $request, FriendService $friendService): View
    {
        $query = $request->input('q');
        /** @var User $user */
        $user = auth()->user();
        $searchDTO = $friendService->searchFriendsWithStatus($query, $user);

        return view('friends.search', $searchDTO->toArray());
    }

    /**
     * Get selected friend ID from request.
     */
    private function getSelectedFriendId(): ?int
    {
        $selectedFriendId = request('friend_id');
        return $selectedFriendId ? (int) $selectedFriendId : null;
    }
}
