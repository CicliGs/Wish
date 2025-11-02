<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FriendService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;

final class FriendsController extends Controller
{
    public function __construct(private readonly Guard $auth) {}

    /**
     * Display friends page
     */
    public function index(FriendService $friendService): View
    {
        $selectedFriendId = $this->getSelectedFriendId();
        $friendsDTO = $friendService->getPageData($this->auth->user(), $selectedFriendId);

        return view('friends.index', $friendsDTO->toArray());
    }

    /**
     * Search friends
     */
    public function search(Request $request, FriendService $friendService): View
    {
        $query = $request->input('search') ?? '';
        $searchDTO = $friendService->searchWithStatus($query, $this->auth->user());

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
