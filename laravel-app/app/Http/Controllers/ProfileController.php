<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\View\View;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ProfileService;
use App\Services\FriendService;
use App\Models\User;
use App\Exceptions\CannotAddSelfAsFriendException;
use App\Exceptions\FriendRequestAlreadySentException;
use App\Exceptions\AlreadyFriendsException;
use App\Exceptions\FriendRequestNotFoundException;
use App\Exceptions\AccessDeniedException;
use App\Exceptions\AvatarUploadFailedException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

final class ProfileController extends BaseController
{
    use AuthorizesRequests;

    private const AVATAR_STORAGE_PATH = 'avatars';

    public function __construct(
        private readonly Guard $auth,
        private readonly FilesystemFactory $filesystem
    ) {}

    /**
     * Display current user profile
     */
    public function showCurrent(ProfileService $profileService, FriendService $friendService): View
    {
        $profileDTO = $profileService->getProfileData($this->auth->user(), $friendService);

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
        $friendService->sendRequest($this->auth->user(), $user);
        $message = __('messages.friend_request_sent');

        if (request()->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
        } catch (CannotAddSelfAsFriendException|FriendRequestAlreadySentException|AlreadyFriendsException $e) {
            $message = $e->getMessage();

            if (request()->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }
    }

    /**
     * Accept friend request
     */
    public function acceptFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        try {
        $friendService->acceptRequest($requestId, $this->auth->id());

        return back()->with('success', __('messages.friend_request_accepted'));
        } catch (FriendRequestNotFoundException|AccessDeniedException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Decline friend request
     */
    public function declineFriendRequest(int $requestId, FriendService $friendService): RedirectResponse
    {
        try {
        $friendService->declineRequest($requestId, $this->auth->id());

        return back()->with('success', __('messages.friend_request_declined'));
        } catch (FriendRequestNotFoundException|AccessDeniedException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove friend
     */
    public function removeFriend(User $user, FriendService $friendService): RedirectResponse
    {
        $friendService->removeFriendship($this->auth->user(), $user);

        return back()->with('success', __('messages.friend_removed'));
    }

    /**
     * Display profile edit form
     */
    public function edit(): View
    {
        return view('profile.edit', ['user' => $this->auth->user()]);
    }

    /**
     * Update user profile
     */
    public function update(UpdateProfileRequest $request, ProfileService $profileService): RedirectResponse
    {
        /** @var User $user */
        $user = $this->auth->user();

        if ($request->has('name') && $request->name !== $user->name) {
            $profileService->updateUserName($user, $request->name);
        }

        if ($request->hasFile('avatar')) {
            $avatarPath = $this->uploadAvatar($request);
            $profileService->updateAvatar($user, $avatarPath);
        }

        return redirect()->route('profile')->with('success', __('messages.profile_updated'));
    }

    /**
     * Upload avatar file and return its public path.
     */
    private function uploadAvatar(UpdateProfileRequest $request): string
    {
        $file = $request->file('avatar');
        $storage = $this->filesystem->disk('public');
        $path = $storage->putFile(self::AVATAR_STORAGE_PATH, $file);

        if ($path === false) {
            throw new AvatarUploadFailedException();
        }

        return $storage->url($path);
    }
}
