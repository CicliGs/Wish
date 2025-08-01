<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Services\ProfileService;
use App\Services\FriendService;
use App\Models\User;
use App\Models\FriendRequest;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    /**
     * Отображает профиль пользователя.
     */
    public function show(ProfileService $profileService, FriendService $friendService): View
    {
        $user = Auth::user();
        $data = $profileService->getProfileData($user, $friendService);
        
        return view('profile', $data);
    }

    /**
     * Отправляет заявку в друзья.
     */
    public function sendFriendRequest(int $userId, FriendService $friendService): RedirectResponse
    {
        $from = Auth::user();
        $result = $friendService->sendRequestToUserId($from, $userId);
        
        return $result === true
            ? back()->with('success', __('messages.friend_request_sent'))
            : back()->with('error', $result);
    }

    /**
     * Принимает заявку в друзья.
     */
    public function acceptFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->acceptRequestById($requestId, Auth::id());
        
        return back()->with('success', __('messages.friend_request_accepted'));
    }

    /**
     * Отклоняет заявку в друзья.
     */
    public function declineFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->declineRequestById($requestId, Auth::id());
        
        return back()->with('success', __('messages.friend_request_declined'));
    }

    /**
     * Удаляет пользователя из друзей.
     */
    public function removeFriend(int $userId, FriendService $friendService): RedirectResponse
    {
        $friendService->removeFriendById(Auth::user(), $userId);
        
        return back()->with('success', __('messages.friend_removed'));
    }

    /**
     * Отображает форму редактирования аватара.
     */
    public function editAvatar(): View
    {
        return view('profile_avatar');
    }

    /**
     * Обновляет аватар пользователя.
     */
    public function updateAvatar(Request $request, ProfileService $profileService): RedirectResponse
    {
        $profileService->updateAvatar(Auth::user(), $request->file('avatar'));
        
        return redirect()->route('profile')->with('success', __('messages.avatar_updated'));
    }

    /**
     * Поиск друзей.
     */
    public function searchFriends(Request $request, ProfileService $profileService): View
    {
        $query = $request->input('q');
        $users = $query ? $profileService->searchFriendsWithStatus($query, Auth::user()) : collect();
        
        return view('friends_search', [
            'users' => $users,
            'query' => $query,
        ]);
    }

    /**
     * Отображает форму редактирования имени.
     */
    public function editName(): View
    {
        return view('profile_edit_name');
    }

    /**
     * Обновляет имя пользователя.
     */
    public function updateName(Request $request, ProfileService $profileService): RedirectResponse
    {
        $profileService->updateUserName(Auth::user(), $request->input('name'));
        
        return redirect()->route('profile')->with('success', __('messages.name_updated'));
    }
}
