<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Exception;
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
        try {
            $result = $friendService->sendFriendRequestToUser(Auth::user(), $user->id);

            if ($this->isAjaxRequest()) {
                return response()->json([
                    'success' => $result === true,
                    'message' => $result === true ? __('messages.friend_request_sent') : $result
                ]);
            }

            return $result === true
                ? back()->with('success', __('messages.friend_request_sent'))
                : back()->with('error', $result);

        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
            }
            return back()->with('error', 'Server error: ' . $e->getMessage());
        }
    }

    /**
     * Accept friend request
     */
    public function acceptFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->acceptFriendRequestById($requestId, Auth::id());
        return back()->with('success', __('messages.friend_request_accepted'));
    }

    /**
     * Decline friend request
     */
    public function declineFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->declineFriendRequestById($requestId, Auth::id());
        return back()->with('success', __('messages.friend_request_declined'));
    }

    /**
     * Remove friend
     */
    public function removeFriend(User $user, FriendService $friendService): RedirectResponse
    {
        $friendService->removeFriendshipBetweenUsers(Auth::user(), $user->id);
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
        $user = Auth::user();

        if ($request->has('name') && $request->name !== $user->name) {
            $profileService->updateUserName($user, $request->name);
        }

        if ($request->hasFile('avatar')) {
            $profileService->updateAvatar($user, $request->file('avatar'));
        }

        return redirect()->route('profile')->with('success', __('messages.profile_updated'));
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return request()->ajax() || request()->wantsJson();
    }
}
