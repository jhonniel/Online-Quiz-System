<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's profile picture.
     */
    public function updateProfilePicture(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        // Use cloud disk + root path
        $assetDisk = 'digitalocean';
        $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        $profileDir = $assetRoot ? $assetRoot . '/profile-pictures' : 'profile-pictures';

        // Delete old profile picture if exists
        if ($user->profile_picture) {
            try {
                Storage::disk($assetDisk)->delete($user->profile_picture);
            } catch (\Throwable $e) {
                // ignore delete errors
            }
        }

        // Store new profile picture
        $path = $request->file('profile_picture')->store($profileDir, $assetDisk);
        $user->profile_picture = $path;
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-picture-updated');
    }

    /**
     * Remove the user's profile picture.
     */
    public function removeProfilePicture(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_picture) {
            try {
                Storage::disk('digitalocean')->delete($user->profile_picture);
            } catch (\Throwable $e) {
                // ignore
            }
            $user->profile_picture = null;
            $user->save();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-picture-removed');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
