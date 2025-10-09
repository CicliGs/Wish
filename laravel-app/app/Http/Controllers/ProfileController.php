<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ProfileService;
use App\Services\FriendService;
use App\Models\User;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display current user profile
     */
    public function showCurrent(ProfileService $profileService, FriendService $friendService): View
    {
        $profileDTO = $profileService->getProfileData(Auth::user(), $friendService);

        return view('profile', $profileDTO->toArray());
    }

    /**
     * Display user profile
     */
    public function show(ProfileService $profileService, FriendService $friendService, User $user): View
    {
        $profileDTO = $profileService->getProfileData($user, $friendService);

        return view('profile', $profileDTO->toArray());
    }

    /**
     * Send friend request
     */
    public function sendFriendRequest(User $user, FriendService $friendService): RedirectResponse|JsonResponse
    {
        $friendService->sendRequest(Auth::user(), $user);
        $message = __('messages.friend_request_sent');
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        
        return back()->with('success', $message);
    }

    /**
     * Accept friend request
     */
    public function acceptFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->acceptRequest($requestId, Auth::id());

        return back()->with('success', __('messages.friend_request_accepted'));
    }

    /**
     * Decline friend request
     */
    public function declineFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->declineRequest($requestId, Auth::id());

        return back()->with('success', __('messages.friend_request_declined'));
    }

    /**
     * Remove friend
     */
    public function removeFriend(User $user, FriendService $friendService): RedirectResponse
    {
        $friendService->removeFriendship(Auth::user(), $user);

        return back()->with('success', __('messages.friend_removed'));
    }

    /**
     * Display profile edit form
     */
    public function edit(): View
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    /**
     * Update user profile
     */
    public function update(UpdateProfileRequest $request, ProfileService $profileService): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($request->has('name') && $request->name !== $user->name) {
            $profileService->updateUserName($user, $request->name);
        }

        if ($request->hasFile('avatar')) {
            $profileService->updateAvatar($user, $request->file('avatar'));
        }

        return redirect()->route('profile')->with('success', __('messages.profile_updated'));
    }
}
