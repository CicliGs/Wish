<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\FriendService;
use Illuminate\Contracts\View\View;

class FriendsController extends Controller
{
    /**
     * Отображение страницы друзей.
     */
    public function index(FriendService $friendService): View
    {
        $user = Auth::user();
        $friendId = request('friend_id');
        $selectedFriendId = $friendId ? (int) $friendId : null;
        $data = $friendService->getFriendsPageData($user, $selectedFriendId);
        
        return view('friends.index', $data);
    }
} 