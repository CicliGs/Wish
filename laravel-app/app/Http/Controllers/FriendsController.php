<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FriendService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendsController extends Controller
{
    /**
     * Display friends page
     */
    public function index(FriendService $friendService): View
    {
        $selectedFriendId = $this->getSelectedFriendId();
        $friendsDTO = $friendService->getPageData(Auth::user(), $selectedFriendId);

        return view('friends.index', $friendsDTO->toArray());
    }

    /**
     * Search friends
     */
    public function search(Request $request, FriendService $friendService): View
    {
        $query = $request->input('search') ?? '';
        $searchDTO = $friendService->searchUsers($query, Auth::user());

        return view('friends.search', $searchDTO->toArray());
    }

    /**
     * Get selected friend ID from request
     */
    private function getSelectedFriendId(): ?int
    {
        $selectedFriendId = request('friend_id');

        return $selectedFriendId ? (int) $selectedFriendId : null;
    }
}
