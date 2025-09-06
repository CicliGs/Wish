<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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

    public function __construct() {}

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
            $result = $friendService->sendFriendRequest(Auth::user(), $user->id);

        } catch (\Exception $e) {
            return $this->handleFriendRequestError($e);
        }

        return $this->handleFriendRequestResponse($result);
    }

    /**
     * Handle friend request response
     */
    private function handleFriendRequestResponse($result): RedirectResponse|JsonResponse
    {
        if ($this->isAjaxRequest()) {
            return $this->createJsonResponse($result);
        }

        return $this->handleFriendRequestResult($result);
    }

    /**
     * Handle friend request error
     */
    private function handleFriendRequestError(\Exception $e): RedirectResponse|JsonResponse
    {
        if ($this->isAjaxRequest()) {
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()])
                ->header('Content-Type', 'application/json');
        }

        return back()->with('error', 'Server error: ' . $e->getMessage());
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return request()->ajax()
            || request()->wantsJson()
            || request()->header('X-Requested-With') === 'XMLHttpRequest'
            || request()->header('Accept') === 'application/json';
    }

    /**
     * Create JSON response for friend request
     */
    private function createJsonResponse($result): JsonResponse
    {
        return $result === true
            ? response()->json(['success' => true, 'message' => __('messages.friend_request_sent')])
                ->header('Content-Type', 'application/json')
            : response()->json(['success' => false, 'message' => $result])
                ->header('Content-Type', 'application/json');
    }

    /**
     * Accept friend request
     */
    public function acceptFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->acceptFriendRequest($requestId, Auth::id());

        return back()->with('success', __('messages.friend_request_accepted'));
    }

    /**
     * Decline friend request
     */
    public function declineFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        $friendService->declineFriendRequest($requestId, Auth::id());

        return back()->with('success', __('messages.friend_request_declined'));
    }

    /**
     * Remove friend
     */
    public function removeFriend(User $user, FriendService $friendService): RedirectResponse
    {
        $friendService->removeFriendship(Auth::user(), $user->id);

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
     * Update user profile (name and avatar)
     * @throws ValidationException
     */
    public function update(Request $request, ProfileService $profileService): RedirectResponse
    {
        $this->validateProfileUpdate($request);
        $this->updateProfileData($request, Auth::user(), $profileService);

        return redirect()->route('profile')->with('success', __('messages.profile_updated'));
    }

    /**
     * Display avatar edit form
     */
    public function editAvatar(): View
    {
        return view('profile_avatar', ['user' => Auth::user()]);
    }

    /**
     * Update user avatar
     * @throws ValidationException
     */
    public function updateAvatar(Request $request, ProfileService $profileService): RedirectResponse
    {
        $profileService->updateAvatar(Auth::user(), $request->file('avatar'));

        return redirect()->route('profile')->with('success', __('messages.avatar_updated'));
    }

    /**
     * Display name edit form
     */
    public function editName(): View
    {
        return view('profile_edit_name', ['user' => Auth::user()]);
    }

    /**
     * Update user name
     * @throws ValidationException
     */
    public function updateName(Request $request, ProfileService $profileService): RedirectResponse
    {
        $profileService->updateUserName(Auth::user(), $request->input('name'));

        return redirect()->route('profile')->with('success', __('messages.name_updated'));
    }

    /**
     * Handle friend request result
     */
    private function handleFriendRequestResult(bool|string $result): RedirectResponse
    {
        return $result === true
            ? back()->with('success', __('messages.friend_request_sent'))
            : back()->with('error', $result);
    }

    /**
     * Validate profile update request
     */
    private function validateProfileUpdate(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|max:' . self::MAX_NAME_LENGTH,
            'avatar' => 'nullable|image|max:' . self::MAX_AVATAR_SIZE,
        ]);
    }

    /**
     * Update profile data
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
     * Check if name should be updated
     */
    private function shouldUpdateName(Request $request, User $user): bool
    {
        $requestName = $request->name;
        $userName = $user->name;

        return $request->has('name') && $requestName !== $userName;
    }
}
