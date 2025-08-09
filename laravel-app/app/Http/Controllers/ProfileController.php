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
use App\DTOs\FriendsSearchDTO;
use Illuminate\Database\Eloquent\Collection;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    private const MAX_AVATAR_SIZE = 2048;
    private const MAX_NAME_LENGTH = 255;

    /**
     * Display user profile.
     */
    public function show(ProfileService $profileService, FriendService $friendService): View
    {
        $profileDTO = $profileService->getProfileData(Auth::user(), $friendService);
        
        return view('profile', $profileDTO->toArray());
    }

    /**
     * Send friend request.
     */
    public function sendFriendRequest(int $userId, FriendService $friendService): RedirectResponse
    {
        $result = $friendService->sendRequestToUserId(Auth::user(), $userId);
        
        return $this->handleFriendRequestResult($result);
    }

    /**
     * Accept friend request.
     */
    public function acceptFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->acceptRequestById($requestId, Auth::id());
        
        return back()->with('success', __('messages.friend_request_accepted'));
    }

    /**
     * Decline friend request.
     */
    public function declineFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->declineRequestById($requestId, Auth::id());
        
        return back()->with('success', __('messages.friend_request_declined'));
    }

    /**
     * Remove friend.
     */
    public function removeFriend(int $userId, FriendService $friendService): RedirectResponse
    {
        $friendService->removeFriendById(Auth::user(), $userId);
        
        return back()->with('success', __('messages.friend_removed'));
    }

    /**
     * Display profile edit form.
     */
    public function edit(): View
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    /**
     * Update user profile (name and avatar).
     */
    public function update(Request $request, ProfileService $profileService): RedirectResponse
    {
        $user = Auth::user();
        
        $this->validateProfileUpdate($request);
        $this->updateProfileData($request, $user, $profileService);

        return redirect()->route('profile')->with('success', __('messages.profile_updated'));
    }

    /**
     * Display avatar edit form.
     */
    public function editAvatar(): View
    {
        return view('profile_avatar', ['user' => Auth::user()]);
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(Request $request, ProfileService $profileService): RedirectResponse
    {
        $profileService->updateAvatar(Auth::user(), $request->file('avatar'));
        
        return redirect()->route('profile')->with('success', __('messages.avatar_updated'));
    }

    /**
     * Search friends.
     */
    public function searchFriends(Request $request, ProfileService $profileService): View
    {
        $query = $request->input('q');
        $searchDTO = $this->createSearchDTO($query, $profileService);
        
        return view('friends_search', $searchDTO->toArray());
    }

    /**
     * Display name edit form.
     */
    public function editName(): View
    {
        return view('profile_edit_name', ['user' => Auth::user()]);
    }

    /**
     * Update user name.
     */
    public function updateName(Request $request, ProfileService $profileService): RedirectResponse
    {
        $profileService->updateUserName(Auth::user(), $request->input('name'));
        
        return redirect()->route('profile')->with('success', __('messages.name_updated'));
    }

    /**
     * Handle friend request result.
     */
    private function handleFriendRequestResult(bool|string $result): RedirectResponse
    {
        return $result === true
            ? back()->with('success', __('messages.friend_request_sent'))
            : back()->with('error', $result);
    }

    /**
     * Validate profile update request.
     */
    private function validateProfileUpdate(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|max:' . self::MAX_NAME_LENGTH,
            'avatar' => 'nullable|image|max:' . self::MAX_AVATAR_SIZE,
        ]);
    }

    /**
     * Update profile data.
     */
    private function updateProfileData(Request $request, User $user, ProfileService $profileService): void
    {
        if ($this->shouldUpdateName($request, $user)) {
            $profileService->updateUserName($user, $request->name);
        }

        if ($request->hasFile('avatar')) {
            $profileService->updateAvatar($user, $request->file('avatar'));
        }
    }

    /**
     * Check if name should be updated.
     */
    private function shouldUpdateName(Request $request, User $user): bool
    {
        return $request->has('name') && $request->name !== $user->name;
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
