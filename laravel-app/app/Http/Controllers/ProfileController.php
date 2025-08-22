<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Services\ProfileService;
use App\Services\FriendService;
use App\Models\User;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    private const MAX_AVATAR_SIZE = 2048;
    private const MAX_NAME_LENGTH = 255;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user profile.
     */
    public function show(ProfileService $profileService, FriendService $friendService, User $user = null): View
    {
        /** @var User $user */
        $user = $user ?? auth()->user();
        $profileDTO = $profileService->getProfileData($user, $friendService);

        return view('profile', $profileDTO->toArray());
    }

    /**
     * Send friend request.
     */
    public function sendFriendRequest(User $user, FriendService $friendService): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $userId = $user->id;
        $result = $friendService->sendRequestToUserId($currentUser, $userId);

        return $this->handleFriendRequestResult($result);
    }

    /**
     * Accept friend request.
     */
    public function acceptFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->acceptRequestById($requestId, auth()->id());

        return back()->with('success', __('messages.friend_request_accepted'));
    }

    /**
     * Decline friend request.
     */
    public function declineFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->declineRequestById($requestId, auth()->id());

        return back()->with('success', __('messages.friend_request_declined'));
    }

    /**
     * Remove friend.
     */
    public function removeFriend(User $user, FriendService $friendService): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $userId = $user->id;
        $friendService->removeFriendById($currentUser, $userId);

        return back()->with('success', __('messages.friend_removed'));
    }

    /**
     * Display profile edit form.
     */
    public function edit(): View
    {
        /** @var User $user */
        $user = auth()->user();
        return view('profile.edit', ['user' => $user]);
    }

    /**
     * Update user profile (name and avatar).
     * @throws ValidationException
     */
    public function update(Request $request, ProfileService $profileService): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $this->validateProfileUpdate($request);
        $this->updateProfileData($request, $user, $profileService);

        return redirect()->route('profile')->with('success', __('messages.profile_updated'));
    }

    /**
     * Display avatar edit form.
     */
    public function editAvatar(): View
    {
        /** @var User $user */
        $user = auth()->user();
        return view('profile_avatar', ['user' => $user]);
    }

    /**
     * Update user avatar.
     * @throws ValidationException
     */
    public function updateAvatar(Request $request, ProfileService $profileService): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $profileService->updateAvatar($user, $request->file('avatar'));

        return redirect()->route('profile')->with('success', __('messages.avatar_updated'));
    }

    /**
     * Display name edit form.
     */
    public function editName(): View
    {
        /** @var User $user */
        $user = auth()->user();
        return view('profile_edit_name', ['user' => $user]);
    }

    /**
     * Update user name.
     * @throws ValidationException
     */
    public function updateName(Request $request, ProfileService $profileService): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $profileService->updateUserName($user, $request->input('name'));

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
     * @throws ValidationException
     */
    private function updateProfileData(Request $request, User $user, ProfileService $profileService): void
    {
        if ($this->shouldUpdateName($request, $user)) {
            $newName = $request->name;
            $profileService->updateUserName($user, $newName);
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
        /** @var string|null $requestName */
        $requestName = $request->name;
        $userName = $user->name;
        return $request->has('name') && $requestName !== $userName;
    }
}
