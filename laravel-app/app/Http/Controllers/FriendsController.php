<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\FriendService;
use App\Services\ProfileService;
use App\DTOs\FriendsSearchDTO;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class FriendsController extends Controller
{
    /**
     * Display friends page.
     */
    public function index(FriendService $friendService): View
    {
        $user = Auth::user();
        $selectedFriendId = $this->getSelectedFriendId();
        $friendsDTO = $friendService->getFriendsPageData($user, $selectedFriendId);
        
        return view('friends.index', $friendsDTO->toArray());
    }

    /**
     * Search friends.
     */
    public function search(Request $request, ProfileService $profileService): View
    {
        $query = $request->input('q');
        $searchDTO = $this->createSearchDTO($query, $profileService);
        
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

    /**
     * Create search DTO for friends search.
     */
    private function createSearchDTO(?string $query, ProfileService $profileService): FriendsSearchDTO
    {
        if (!$query) {
            return new FriendsSearchDTO(new Collection(), null);
        }

        return $profileService->searchFriendsWithStatus($query, Auth::user());
    }
} 