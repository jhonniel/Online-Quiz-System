@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col space-y-3 min-h-0">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Edit Profile</h1>
                    <p class="text-indigo-100 text-sm">Update your profile information and photos</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('profile.show') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span class="hidden sm:inline">View Profile</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mx-2 sm:mx-3 lg:mx-4 xl:mx-6 flex-1 overflow-hidden">
        <form id="profile-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="h-full flex flex-col">
            @csrf
            @method('PUT')

            <div class="flex-1 overflow-y-auto p-6">
                <!-- Cover Photo Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Cover Photo</h3>
                    <div class="relative">
                        <div class="h-32 sm:h-40 bg-gradient-to-r from-blue-400 via-purple-500 to-pink-500 rounded-lg overflow-hidden">
                            @if($user->hasCoverPhoto())
                                <img id="cover-preview" src="{{ $user->getCoverPhotoUrl() }}"
                                     alt="Cover Photo"
                                     class="w-full h-full object-cover">
                            @else
                                <div id="cover-placeholder" class="w-full h-full bg-gradient-to-r from-blue-400 via-purple-500 to-pink-500 flex items-center justify-center">
                                    <div class="text-center text-white">
                                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-sm opacity-75">No cover photo</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-3 flex flex-col sm:flex-row gap-3">
                            <label for="cover_photo" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Upload Cover Photo
                            </label>
                            <input type="file" id="cover_photo" name="cover_photo" accept="image/*" class="hidden" onchange="previewCoverPhoto(this)">

                            @if($user->hasCoverPhoto())
                                <button type="button" onclick="removeCoverPhoto()" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Remove Cover Photo
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Profile Picture Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Picture</h3>
                    <div class="flex items-center space-x-6">
                        <div class="relative">
                            @if($user->profile_picture)
                                <img id="profile-preview" src="{{ $user->getProfilePictureUrl() }}"
                                     alt="Profile Picture"
                                     class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                            @else
                                <div id="profile-placeholder" class="w-24 h-24 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-xl">
                                        {{ $user->getInitials() }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col space-y-2">
                            <label for="profile_picture" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Upload Profile Picture
                            </label>
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden" onchange="previewProfilePicture(this)">

                            @if($user->profile_picture)
                                <button type="button" onclick="removeProfilePicture()" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Remove Profile Picture
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-300 @enderror"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email"
                                   id="email"
                                   value="{{ $user->email }}"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500 sm:text-sm"
                                   disabled>
                            <p class="mt-1 text-sm text-gray-500">Email cannot be changed</p>
                        </div>
                    </div>
                </div>

                <!-- Bio Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">About Me</h3>
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                        <textarea id="bio"
                                  name="bio"
                                  rows="4"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('bio') border-red-300 @enderror"
                                  placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">Maximum 1000 characters</p>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Change Password Section -->
                <div class="mb-8 border-t border-gray-200 pt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                    <form id="password-form" action="{{ route('profile.password.change') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('current_password') border-red-300 @enderror"
                                   required>
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                            <input type="password"
                                   id="new_password"
                                   name="new_password"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('new_password') border-red-300 @enderror"
                                   required>
                            <p class="mt-1 text-sm text-gray-500">Must be at least 8 characters</p>
                            @error('new_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password"
                                   id="new_password_confirmation"
                                   name="new_password_confirmation"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                        </div>

                        <div>
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <a href="{{ route('profile.show') }}"
                       class="inline-flex items-center justify-center px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification -->
<x-toast />

<script>
// Profile Picture Preview
function previewProfilePicture(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('profile-preview');
            const placeholder = document.getElementById('profile-placeholder');

            if (preview) {
                preview.src = e.target.result;
            } else {
                placeholder.innerHTML = `<img src="${e.target.result}" alt="Profile Picture" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">`;
                placeholder.id = 'profile-preview';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Cover Photo Preview
function previewCoverPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('cover-preview');
            const placeholder = document.getElementById('cover-placeholder');

            if (preview) {
                preview.src = e.target.result;
            } else {
                placeholder.innerHTML = `<img src="${e.target.result}" alt="Cover Photo" class="w-full h-full object-cover">`;
                placeholder.id = 'cover-preview';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Remove Profile Picture
function removeProfilePicture() {
    if (confirm('Are you sure you want to remove your profile picture?')) {
        fetch('{{ route("profile.picture.remove") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const preview = document.getElementById('profile-preview');
                const placeholder = document.getElementById('profile-placeholder');

                if (preview) {
                    preview.outerHTML = `<div id="profile-placeholder" class="w-24 h-24 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center">
                        <span class="text-white font-bold text-xl">{{ $user->getInitials() }}</span>
                    </div>`;
                }

                ToastNotification.success(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ToastNotification.error('Failed to remove profile picture');
        });
    }
}

// Remove Cover Photo
function removeCoverPhoto() {
    if (confirm('Are you sure you want to remove your cover photo?')) {
        fetch('{{ route("profile.cover.remove") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const preview = document.getElementById('cover-preview');
                const placeholder = document.getElementById('cover-placeholder');

                if (preview) {
                    preview.outerHTML = `<div id="cover-placeholder" class="w-full h-full bg-gradient-to-r from-blue-400 via-purple-500 to-pink-500 flex items-center justify-center">
                        <div class="text-center text-white">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm opacity-75">No cover photo</p>
                        </div>
                    </div>`;
                }

                ToastNotification.success(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ToastNotification.error('Failed to remove cover photo');
        });
    }
}

// Form Submission
document.getElementById('profile-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;

    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...';

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            ToastNotification.success(data.message);
            setTimeout(() => {
                window.location.href = data.redirect_url;
            }, 1500);
        } else {
            ToastNotification.error(data.message || 'Failed to update profile');
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = 'Failed to update profile';

        if (error.errors) {
            // Handle validation errors
            const firstError = Object.values(error.errors)[0];
            errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
        } else if (error.message) {
            errorMessage = error.message;
        }

        ToastNotification.error(errorMessage);

        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});

// Password Change Form Submission
document.getElementById('password-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;

    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Changing...';

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            ToastNotification.success(data.message);
            // Reset form
            this.reset();
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        } else {
            ToastNotification.error(data.message || 'Failed to change password');
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = 'Failed to change password';

        if (error.errors) {
            // Handle validation errors
            const firstError = Object.values(error.errors)[0];
            errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
        } else if (error.message) {
            errorMessage = error.message;
        }

        ToastNotification.error(errorMessage);

        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});
</script>
@endsection
