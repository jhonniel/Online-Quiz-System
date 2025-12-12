<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        // Get friend request data - only pending requests
        $pendingRequests = $user->pendingFriendRequests()->with('user')->get();
        $sentRequests = $user->sentFriendRequests()->with('friend')->get();

        // Get friends list from both directions
        $friendsAsUser = $user->friends()->get();
        $friendsAsFriend = $user->acceptedFriends()->get();
        $allFriends = $friendsAsUser->merge($friendsAsFriend)->unique('id');

        return view('user.profile.show', compact('user', 'pendingRequests', 'sentRequests', 'allFriends'));
    }

    public function edit()
    {
        $user = auth()->user();
        return view('user.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'name' => 'required|string|max:255',
                'bio' => 'nullable|string|max:1000',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max for cover photo
            ], [
                'profile_picture.image' => 'Profile picture must be an image file.',
                'profile_picture.mimes' => 'Profile picture must be a JPEG, PNG, JPG, GIF, or WEBP file.',
                'profile_picture.max' => 'Profile picture must not be larger than 2MB.',
                'cover_photo.image' => 'Cover photo must be an image file.',
                'cover_photo.mimes' => 'Cover photo must be a JPEG, PNG, JPG, GIF, or WEBP file.',
                'cover_photo.max' => 'Cover photo must not be larger than 5MB.',
            ]);

            $data = [
                'name' => $request->name,
                'bio' => $request->bio,
            ];

            // Cloud disk + root path
            $assetDisk = 'digitalocean';
            $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
            $profileDir = $assetRoot ? $assetRoot . '/profile-pictures' : 'profile-pictures';
            $coverDir = $assetRoot ? $assetRoot . '/cover-photos' : 'cover-photos';

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture) {
                    try {
                        Storage::disk($assetDisk)->delete($user->profile_picture);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                $profilePicture = $request->file('profile_picture');
                $profilePictureName = time() . '_' . Str::random(10) . '.' . $profilePicture->getClientOriginalExtension();
                $profilePicturePath = $profilePicture->storeAs($profileDir, $profilePictureName, $assetDisk);
                $data['profile_picture'] = $profilePicturePath;
            }

            // Handle cover photo upload
            if ($request->hasFile('cover_photo')) {
                // Delete old cover photo if exists
                if ($user->cover_photo) {
                    try {
                        Storage::disk($assetDisk)->delete($user->cover_photo);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                $coverPhoto = $request->file('cover_photo');
                $coverPhotoName = time() . '_' . Str::random(10) . '.' . $coverPhoto->getClientOriginalExtension();
                $coverPhotoPath = $coverPhoto->storeAs($coverDir, $coverPhotoName, $assetDisk);
                $data['cover_photo'] = $coverPhotoPath;
            }

            $user->update($data);

            // Check if request expects JSON response (AJAX/fetch)
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully!',
                    'type' => 'success',
                    'redirect_url' => route('profile.show')
                ]);
            }

            return redirect()->route('profile.show')
                ->with('success', 'Profile updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    public function removeProfilePicture()
    {
        $user = auth()->user();

        if ($user->profile_picture) {
            try {
                Storage::disk('digitalocean')->delete($user->profile_picture);
            } catch (\Throwable $e) {
                // ignore
            }
            $user->update(['profile_picture' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile picture removed successfully!',
            'type' => 'success'
        ]);
    }

    public function removeCoverPhoto()
    {
        $user = auth()->user();

        if ($user->cover_photo) {
            try {
                Storage::disk('digitalocean')->delete($user->cover_photo);
            } catch (\Throwable $e) {
                // ignore
            }
            $user->update(['cover_photo' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cover photo removed successfully!',
            'type' => 'success'
        ]);
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ], [
                'current_password.required' => 'Current password is required.',
                'new_password.required' => 'New password is required.',
                'new_password.min' => 'New password must be at least 8 characters.',
                'new_password.confirmed' => 'New password confirmation does not match.',
            ]);

            $user = auth()->user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect.',
                        'errors' => ['current_password' => ['Current password is incorrect.']]
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password changed successfully!',
                    'type' => 'success'
                ]);
            }

            return redirect()->back()
                ->with('success', 'Password changed successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to change password: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to change password: ' . $e->getMessage());
        }
    }
}
