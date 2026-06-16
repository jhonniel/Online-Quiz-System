<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\StoryService;
use App\Support\UserThemeColor;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        // Get friend request data - only pending requests
        $pendingRequests = $user->pendingFriendRequests()->with(['user.department:id,name'])->get();
        $sentRequests = $user->sentFriendRequests()->with(['friend.department:id,name'])->get();

        // Get friends list from both directions
        $friendsAsUser = $user->friends()->with('department:id,name')->get();
        $friendsAsFriend = $user->acceptedFriends()->with('department:id,name')->get();
        $allFriends = $friendsAsUser->merge($friendsAsFriend)->unique('id');

        $storyFeed = StoryService::feedFor($user);
        $selfHasStory = StoryService::hasActiveStory($user->id);
        $storyRingMap = StoryService::ringMapFor(
            $user,
            $allFriends->pluck('id')->push($user->id)->unique()->values()->all()
        );

        return view('user.profile.show', compact(
            'user',
            'pendingRequests',
            'sentRequests',
            'allFriends',
            'storyFeed',
            'selfHasStory',
            'storyRingMap'
        ));
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

            $rules = [
                'name' => 'required|string|max:255',
                'bio' => 'nullable|string|max:1000',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max for cover photo
                'e_signature' => 'nullable|file|mimes:png|max:1536',
            ];

            if ($user->canCustomizeThemeColor()) {
                $rules['theme_color'] = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];
            }

            if ($user->isEmployee()) {
                $rules['gender'] = 'nullable|in:'.implode(',', array_keys(User::GENDERS));
            }

            $request->validate($rules, [
                'profile_picture.image' => 'Profile picture must be an image file.',
                'profile_picture.mimes' => 'Profile picture must be a JPEG, PNG, JPG, GIF, or WEBP file.',
                'profile_picture.max' => 'Profile picture must not be larger than 2MB.',
                'cover_photo.image' => 'Cover photo must be an image file.',
                'cover_photo.mimes' => 'Cover photo must be a JPEG, PNG, JPG, GIF, or WEBP file.',
                'cover_photo.max' => 'Cover photo must not be larger than 5MB.',
                'e_signature.mimes' => 'E-signature must be a PNG file.',
                'e_signature.max' => 'E-signature must not be larger than 1.5MB.',
                'theme_color.regex' => 'Theme color must be a valid hex color (e.g. #4F46E5).',
                'gender.in' => 'Please select a valid gender option.',
            ]);

            $data = [
                'name' => $request->name,
                'bio' => $request->bio,
            ];

            if ($user->isEmployee()) {
                $data['gender'] = $request->filled('gender') ? $request->input('gender') : null;
            }

            if ($user->canCustomizeThemeColor()) {
                $data['theme_color'] = UserThemeColor::normalize($request->input('theme_color'));
            }

            // Store profile picture and cover on DigitalOcean Spaces when configured, else public disk
            $spacesConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
                && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
                && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));
            $assetDisk = $spacesConfigured ? 'digitalocean' : 'public';
            $assetRoot = $spacesConfigured ? trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/') : '';
            $profileDir = $assetRoot ? $assetRoot.'/profile-pictures' : 'profile-pictures';
            $coverDir = $assetRoot ? $assetRoot.'/cover-photos' : 'cover-photos';
            $eSignatureDir = $assetRoot ? $assetRoot.'/e-signatures' : 'e-signatures';

            // Handle profile picture upload (stored on Spaces when configured)
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists (try both disks for legacy files)
                if ($user->profile_picture) {
                    foreach (['digitalocean', 'public'] as $disk) {
                        try {
                            if (Storage::disk($disk)->exists($user->profile_picture)) {
                                Storage::disk($disk)->delete($user->profile_picture);
                                break;
                            }
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    }
                }

                $profilePicture = $request->file('profile_picture');
                $profilePictureName = time().'_'.Str::random(10).'.'.$profilePicture->getClientOriginalExtension();
                $profilePicturePath = $profilePicture->storeAs($profileDir, $profilePictureName, $assetDisk);
                $data['profile_picture'] = $profilePicturePath;
            }

            // Handle cover photo upload (stored on Spaces when configured)
            if ($request->hasFile('cover_photo')) {
                // Delete old cover photo if exists (try both disks for legacy files)
                if ($user->cover_photo) {
                    foreach (['digitalocean', 'public'] as $disk) {
                        try {
                            if (Storage::disk($disk)->exists($user->cover_photo)) {
                                Storage::disk($disk)->delete($user->cover_photo);
                                break;
                            }
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    }
                }

                $coverPhoto = $request->file('cover_photo');
                $coverPhotoName = time().'_'.Str::random(10).'.'.$coverPhoto->getClientOriginalExtension();
                $coverPhotoPath = $coverPhoto->storeAs($coverDir, $coverPhotoName, $assetDisk);
                $data['cover_photo'] = $coverPhotoPath;
            }

            if ($request->boolean('e_signature_expected') && ! $request->hasFile('e_signature')) {
                throw ValidationException::withMessages([
                    'e_signature' => [$this->missingUploadMessage('e_signature')],
                ]);
            }

            if ($request->hasFile('e_signature')) {
                $data['e_signature_path'] = $this->storeESignatureFile(
                    $user,
                    $request->file('e_signature'),
                    $assetDisk,
                    $eSignatureDir
                );
            }

            $user->update($data);
            $user->refresh();

            // Check if request expects JSON response (AJAX/fetch)
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully!',
                    'type' => 'success',
                    'redirect_url' => url('/profile'),
                    'e_signature_url' => $user->hasESignature() ? $user->getESignatureUrl() : null,
                ]);
            }

            return redirect('/profile')
                ->with('success', 'Profile updated successfully!');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to update profile: '.$e->getMessage());
        }
    }

    public function removeProfilePicture()
    {
        $user = auth()->user();

        if ($user->profile_picture) {
            foreach (['digitalocean', 'public'] as $disk) {
                try {
                    if (Storage::disk($disk)->exists($user->profile_picture)) {
                        Storage::disk($disk)->delete($user->profile_picture);
                        break;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
            $user->update(['profile_picture' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile picture removed successfully!',
            'type' => 'success',
        ]);
    }

    public function removeCoverPhoto()
    {
        $user = auth()->user();

        if ($user->cover_photo) {
            foreach (['digitalocean', 'public'] as $disk) {
                try {
                    if (Storage::disk($disk)->exists($user->cover_photo)) {
                        Storage::disk($disk)->delete($user->cover_photo);
                        break;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
            $user->update(['cover_photo' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cover photo removed successfully!',
            'type' => 'success',
        ]);
    }

    public function uploadESignature(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'e_signature' => 'required|file|mimes:png|max:1536',
            ], [
                'e_signature.required' => 'Please choose a PNG e-signature file.',
                'e_signature.mimes' => 'E-signature must be a PNG file.',
                'e_signature.max' => 'E-signature must not be larger than 1.5MB.',
            ]);

            if (! $request->hasFile('e_signature')) {
                throw ValidationException::withMessages([
                    'e_signature' => [$this->missingUploadMessage('e_signature')],
                ]);
            }

            ['disk' => $assetDisk, 'e_signature_dir' => $eSignatureDir] = $this->assetStorageContext();
            $path = $this->storeESignatureFile($user, $request->file('e_signature'), $assetDisk, $eSignatureDir);
            $user->update(['e_signature_path' => $path]);
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'E-signature saved successfully!',
                'type' => 'success',
                'e_signature_url' => $user->getESignatureUrl(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save e-signature: '.$e->getMessage(),
            ], 500);
        }
    }

    public function removeESignature()
    {
        $user = auth()->user();

        $this->deleteStoredAsset($user->e_signature_path);
        $user->update(['e_signature_path' => null]);

        return response()->json([
            'success' => true,
            'message' => 'E-signature removed successfully!',
            'type' => 'success',
        ]);
    }

    public function uploadP12Certificate(Request $request)
    {
        try {
            $user = auth()->user();

            if ($user->role !== 'employee') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only employees can upload a P12 certificate.',
                ], 403);
            }

            $request->validate([
                'p12_certificate' => 'required|file|max:5120',
                'p12_certificate_password' => 'required|string|max:255',
            ], [
                'p12_certificate.required' => 'Please choose a P12 or PFX certificate file.',
                'p12_certificate.max' => 'Certificate file must not be larger than 5MB.',
                'p12_certificate_password.required' => 'Certificate password is required.',
            ]);

            if (! $request->hasFile('p12_certificate')) {
                throw ValidationException::withMessages([
                    'p12_certificate' => [$this->missingUploadMessage('p12_certificate', 'P12 certificate')],
                ]);
            }

            $file = $request->file('p12_certificate');
            $extension = strtolower($file->getClientOriginalExtension());

            if (! in_array($extension, ['p12', 'pfx'], true)) {
                throw ValidationException::withMessages([
                    'p12_certificate' => ['Certificate must be a .p12 or .pfx file.'],
                ]);
            }

            $password = (string) $request->input('p12_certificate_password');
            $contents = file_get_contents($file->getRealPath());
            $certs = [];

            if (! is_string($contents) || $contents === '' || ! openssl_pkcs12_read($contents, $certs, $password)) {
                throw ValidationException::withMessages([
                    'p12_certificate_password' => ['The certificate file or password is invalid.'],
                ]);
            }

            ['disk' => $assetDisk, 'p12_certificate_dir' => $p12Dir] = $this->assetStorageContext();
            $path = $this->storeP12CertificateFile($user, $file, $assetDisk, $p12Dir, $extension);

            $user->update([
                'p12_certificate_path' => $path,
                'p12_certificate_password' => $password,
            ]);
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'P12 certificate saved successfully!',
                'type' => 'success',
                'has_p12_certificate' => $user->hasP12Certificate(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save P12 certificate: '.$e->getMessage(),
            ], 500);
        }
    }

    public function removeP12Certificate()
    {
        $user = auth()->user();

        if ($user->role !== 'employee') {
            return response()->json([
                'success' => false,
                'message' => 'Only employees can remove a P12 certificate.',
            ], 403);
        }

        $this->deleteStoredAsset($user->p12_certificate_path);
        $user->update([
            'p12_certificate_path' => null,
            'p12_certificate_password' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'P12 certificate removed successfully!',
            'type' => 'success',
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
            if (! Hash::check($request->current_password, $user->password)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect.',
                        'errors' => ['current_password' => ['Current password is incorrect.']],
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
                    'type' => 'success',
                ]);
            }

            return redirect()->back()
                ->with('success', 'Password changed successfully!');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to change password: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to change password: '.$e->getMessage());
        }
    }

    /**
     * @return array{disk: string, root: string, profile_dir: string, cover_dir: string, e_signature_dir: string, p12_certificate_dir: string}
     */
    private function assetStorageContext(): array
    {
        $spacesConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
            && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
            && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));
        $assetDisk = $spacesConfigured ? 'digitalocean' : 'public';
        $assetRoot = $spacesConfigured ? trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/') : '';

        return [
            'disk' => $assetDisk,
            'root' => $assetRoot,
            'profile_dir' => $assetRoot ? $assetRoot.'/profile-pictures' : 'profile-pictures',
            'cover_dir' => $assetRoot ? $assetRoot.'/cover-photos' : 'cover-photos',
            'e_signature_dir' => $assetRoot ? $assetRoot.'/e-signatures' : 'e-signatures',
            'p12_certificate_dir' => $assetRoot ? $assetRoot.'/p12-certificates' : 'p12-certificates',
        ];
    }

    private function storeESignatureFile(User $user, UploadedFile $file, string $disk, string $directory): string
    {
        $this->deleteStoredAsset($user->e_signature_path);

        $fileName = time().'_'.Str::random(10).'.png';
        $storedPath = $file->storeAs($directory, $fileName, $disk);

        if (! is_string($storedPath) || $storedPath === '') {
            throw new \RuntimeException('Failed to upload e-signature to storage.');
        }

        return $storedPath;
    }

    private function storeP12CertificateFile(User $user, UploadedFile $file, string $disk, string $directory, string $extension): string
    {
        $this->deleteStoredAsset($user->p12_certificate_path);

        $fileName = time().'_'.Str::random(10).'.'.strtolower($extension);
        $storedPath = $file->storeAs($directory, $fileName, $disk);

        if (! is_string($storedPath) || $storedPath === '') {
            throw new \RuntimeException('Failed to upload P12 certificate to storage.');
        }

        return $storedPath;
    }

    private function deleteStoredAsset(?string $path): void
    {
        if (! $path) {
            return;
        }

        foreach (['digitalocean', 'public'] as $storageDisk) {
            try {
                if (Storage::disk($storageDisk)->exists($path)) {
                    Storage::disk($storageDisk)->delete($path);
                    break;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    private function missingUploadMessage(string $field, string $label = 'E-signature'): string
    {
        $error = $_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE;

        return match ((int) $error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "The {$label} file is too large for the server upload limit.",
            UPLOAD_ERR_PARTIAL => "The {$label} upload was interrupted. Please try again.",
            UPLOAD_ERR_NO_FILE => "The {$label} file was not received. Please choose the file again and save.",
            default => "The {$label} file could not be uploaded. Please try again.",
        };
    }
}
