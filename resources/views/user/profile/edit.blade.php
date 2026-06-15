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
                <a href="{{ url('/profile') }}"
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
        <form id="profile-form" action="{{ url('/profile') }}" method="POST" enctype="multipart/form-data" class="h-full flex flex-col">
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

                <!-- E-Signature Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">E-Signature</h3>
                    <p class="text-sm text-gray-500 mb-4">Upload a PNG image of your signature. It will be stored securely and can be used on documents.</p>
                    <div class="flex flex-col sm:flex-row sm:items-start gap-6">
                        <div class="flex-shrink-0">
                            @if($user->hasESignature())
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 inline-block">
                                    <img id="e-signature-preview"
                                         src="{{ $user->getESignatureUrl() }}"
                                         alt="E-Signature"
                                         class="max-h-24 max-w-xs object-contain">
                                </div>
                            @else
                                <div id="e-signature-placeholder"
                                     class="w-48 h-24 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 flex items-center justify-center">
                                    <div class="text-center text-gray-400 px-3">
                                        <svg class="w-8 h-8 mx-auto mb-1 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                        <p class="text-xs">No e-signature</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col space-y-2">
                            <label for="e_signature" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Upload E-Signature
                            </label>
                            <input type="file"
                                   id="e_signature"
                                   name="e_signature"
                                   accept="image/png,.png"
                                   class="hidden"
                                   onchange="previewESignature(this)">
                            <p class="text-xs text-gray-500">PNG only, up to 1.5MB. Saves automatically when selected.</p>
                            <p id="e-signature-status" class="text-xs text-gray-500 hidden"></p>
                            @error('e_signature')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            @if($user->hasESignature())
                                <button type="button"
                                        onclick="removeESignature()"
                                        class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Remove E-Signature
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                @if($user->isStaffMember())
                <!-- P12 Digital Certificate -->
                <div class="mb-8" id="p12-certificate-section">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">P12 Digital Certificate</h3>
                    <p class="text-sm text-gray-500 mb-4">Upload your PKCS#12 (.p12 or .pfx) certificate for digitally signing employee documents. Stored securely on cloud storage.</p>
                    <div class="flex flex-col sm:flex-row sm:items-start gap-6">
                        <div class="flex-shrink-0">
                            <div id="p12-certificate-status-card"
                                 class="w-48 rounded-lg border-2 {{ $user->hasP12Certificate() ? 'border-green-200 bg-green-50' : 'border-dashed border-gray-300 bg-gray-50' }} p-4 flex items-center justify-center min-h-[6rem]">
                                @if($user->hasP12Certificate())
                                    <div class="text-center text-green-700">
                                        <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        <p class="text-sm font-medium">Certificate on file</p>
                                    </div>
                                @else
                                    <div class="text-center text-gray-400 px-3">
                                        <svg class="w-8 h-8 mx-auto mb-1 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <p class="text-xs">No certificate</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col space-y-3 flex-1 max-w-md">
                            <div>
                                <label for="p12_certificate" class="block text-sm font-medium text-gray-700 mb-1">Certificate file</label>
                                <input type="file"
                                       id="p12_certificate"
                                       name="p12_certificate"
                                       accept=".p12,.pfx,application/x-pkcs12"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <p class="mt-1 text-xs text-gray-500">.p12 or .pfx only, up to 5MB.</p>
                            </div>
                            <div>
                                <label for="p12_certificate_password" class="block text-sm font-medium text-gray-700 mb-1">Certificate password</label>
                                <input type="password"
                                       id="p12_certificate_password"
                                       name="p12_certificate_password"
                                       autocomplete="new-password"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="Enter certificate password">
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button"
                                        onclick="uploadP12Certificate()"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Save Certificate
                                </button>
                                @if($user->hasP12Certificate())
                                    <button type="button"
                                            id="remove-p12-certificate-btn"
                                            onclick="removeP12Certificate()"
                                            class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Remove Certificate
                                    </button>
                                @endif
                            </div>
                            <p id="p12-certificate-status" class="text-xs text-gray-500 hidden"></p>
                        </div>
                    </div>
                </div>
                @endif

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

                @if($user->canCustomizeThemeColor())
                    @php
                        $accountThemeColor = old('theme_color', $user->theme_color ?: \App\Support\UserThemeColor::DEFAULT);
                    @endphp
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Account Theme</h3>
                        <p class="text-sm text-gray-500 mb-4">Color for your sidebar, navigation, buttons, banners, charts, and lists.</p>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <label for="theme_color" class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="color"
                                       name="theme_color"
                                       id="theme_color"
                                       value="{{ $accountThemeColor }}"
                                       class="h-12 w-16 rounded-lg border border-gray-300 cursor-pointer bg-white p-1">
                                <span class="text-sm font-medium text-gray-700">Account color</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <label for="theme_color_hex" class="sr-only">Account color hex</label>
                                <input type="text"
                                       id="theme_color_hex"
                                       value="{{ strtoupper($accountThemeColor) }}"
                                       maxlength="7"
                                       pattern="^#[0-9A-Fa-f]{6}$"
                                       class="block w-28 px-3 py-2 border border-gray-300 rounded-md shadow-sm font-mono text-sm uppercase focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('theme_color') border-red-300 @enderror">
                            </div>
                            <div id="theme-color-preview"
                                 class="h-10 w-10 rounded-full border-2 border-white shadow-md ring-1 ring-gray-200"
                                 style="background-color: {{ $accountThemeColor }};"
                                 title="Account color preview"></div>
                        </div>
                        @error('theme_color')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            <!-- Form Actions - Sticky at bottom -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex-shrink-0 sticky bottom-0 z-10">
                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <a href="{{ url('/profile') }}"
                       class="inline-flex items-center justify-center px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit"
                            id="save-profile-btn"
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

    <!-- Change Password Section - Separate Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mx-2 sm:mx-3 lg:mx-4 xl:mx-6 mt-4">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
            <form id="password-form" action="{{ url('/profile/password/change') }}" method="POST" class="space-y-6">
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
</div>

<!-- Toast Notification -->
<x-toast />

<script>
@if($user->canCustomizeThemeColor())
(function () {
    function normalizeHex(value) {
        if (!value) {
            return '';
        }
        let hex = value.trim().toUpperCase();
        if (!hex.startsWith('#')) {
            hex = '#' + hex;
        }
        return /^#[0-9A-F]{6}$/.test(hex) ? hex : '';
    }

    function bindColorPair(colorId, hexId, previewId) {
        const colorInput = document.getElementById(colorId);
        const hexInput = document.getElementById(hexId);
        const preview = document.getElementById(previewId);
        if (!colorInput || !hexInput || !preview) {
            return;
        }

        function syncFromPicker() {
            hexInput.value = colorInput.value.toUpperCase();
            preview.style.backgroundColor = colorInput.value;
        }

        function syncFromHex() {
            const normalized = normalizeHex(hexInput.value);
            if (normalized) {
                colorInput.value = normalized;
                hexInput.value = normalized;
                preview.style.backgroundColor = normalized;
            }
        }

        colorInput.addEventListener('input', syncFromPicker);
        hexInput.addEventListener('change', syncFromHex);
        hexInput.addEventListener('blur', syncFromHex);
    }

    bindColorPair('theme_color', 'theme_color_hex', 'theme-color-preview');

    document.getElementById('profile-form')?.addEventListener('submit', function () {
        const colorInput = document.getElementById('theme_color');
        const hexInput = document.getElementById('theme_color_hex');
        const normalized = normalizeHex(hexInput?.value || colorInput?.value || '');
        if (normalized && colorInput) {
            colorInput.value = normalized;
        }
    });
})();
@endif

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

const E_SIGNATURE_MAX_BYTES = 1536 * 1024;

function setESignatureStatus(message, tone = 'muted') {
    const status = document.getElementById('e-signature-status');
    if (!status) {
        return;
    }

    status.textContent = message;
    status.classList.remove('hidden', 'text-gray-500', 'text-green-600', 'text-red-600');
    status.classList.add(tone === 'success' ? 'text-green-600' : tone === 'error' ? 'text-red-600' : 'text-gray-500');
}

function renderESignaturePreview(src) {
    const preview = document.getElementById('e-signature-preview');
    const placeholder = document.getElementById('e-signature-placeholder');

    if (preview) {
        preview.src = src;
        return;
    }

    if (placeholder) {
        placeholder.outerHTML = `<div class="rounded-lg border border-gray-200 bg-gray-50 p-3 inline-block">
            <img id="e-signature-preview" src="${src}" alt="E-Signature" class="max-h-24 max-w-xs object-contain">
        </div>`;
    }
}

function uploadESignatureFile(file) {
    const formData = new FormData();
    formData.append('e_signature', file);

    setESignatureStatus('Saving e-signature...');

    return fetch('{{ url("/profile/e-signature") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            const message = data.errors?.e_signature?.[0] || data.message || 'Failed to save e-signature.';
            throw new Error(message);
        }

        if (data.e_signature_url) {
            renderESignaturePreview(data.e_signature_url);
        }

        setESignatureStatus('E-signature saved.', 'success');
        ToastNotification.success(data.message || 'E-signature saved successfully!');
        return data;
    })
    .catch(error => {
        setESignatureStatus(error.message || 'Failed to save e-signature.', 'error');
        ToastNotification.error(error.message || 'Failed to save e-signature.');
        throw error;
    });
}

// E-Signature Preview + immediate save
function previewESignature(input) {
    if (!input.files || !input.files[0]) {
        return;
    }

    const file = input.files[0];
    const isPng = file.type === 'image/png' || file.name.toLowerCase().endsWith('.png');
    if (!isPng) {
        ToastNotification.error('E-signature must be a PNG file.');
        input.value = '';
        setESignatureStatus('E-signature must be a PNG file.', 'error');
        return;
    }

    if (file.size > E_SIGNATURE_MAX_BYTES) {
        ToastNotification.error('E-signature must not be larger than 1.5MB.');
        input.value = '';
        setESignatureStatus('E-signature must not be larger than 1.5MB.', 'error');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        renderESignaturePreview(e.target.result);
        uploadESignatureFile(file).catch(() => {
            input.value = '';
        });
    };
    reader.readAsDataURL(file);
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
        fetch('{{ url("/profile/picture") }}', {
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

const P12_CERTIFICATE_MAX_BYTES = 5120 * 1024;

function setP12CertificateStatus(message, tone = 'muted') {
    const status = document.getElementById('p12-certificate-status');
    if (!status) {
        return;
    }

    status.textContent = message;
    status.classList.remove('hidden', 'text-gray-500', 'text-green-600', 'text-red-600');
    status.classList.add(tone === 'success' ? 'text-green-600' : tone === 'error' ? 'text-red-600' : 'text-gray-500');
}

function renderP12CertificateOnFile() {
    const card = document.getElementById('p12-certificate-status-card');
    if (!card) {
        return;
    }

    card.className = 'w-48 rounded-lg border-2 border-green-200 bg-green-50 p-4 flex items-center justify-center min-h-[6rem]';
    card.innerHTML = `<div class="text-center text-green-700">
        <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <p class="text-sm font-medium">Certificate on file</p>
    </div>`;

    const section = document.getElementById('p12-certificate-section');
    if (section && !document.getElementById('remove-p12-certificate-btn')) {
        const actions = section.querySelector('.flex.flex-wrap.gap-2');
        if (actions) {
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.id = 'remove-p12-certificate-btn';
            removeBtn.onclick = removeP12Certificate;
            removeBtn.className = 'inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500';
            removeBtn.textContent = 'Remove Certificate';
            actions.appendChild(removeBtn);
        }
    }
}

function renderP12CertificateEmpty() {
    const card = document.getElementById('p12-certificate-status-card');
    if (!card) {
        return;
    }

    card.className = 'w-48 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 flex items-center justify-center min-h-[6rem]';
    card.innerHTML = `<div class="text-center text-gray-400 px-3">
        <svg class="w-8 h-8 mx-auto mb-1 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <p class="text-xs">No certificate</p>
    </div>`;

    document.getElementById('remove-p12-certificate-btn')?.remove();
}

function uploadP12Certificate() {
    const fileInput = document.getElementById('p12_certificate');
    const passwordInput = document.getElementById('p12_certificate_password');

    if (!fileInput?.files?.[0]) {
        ToastNotification.error('Please choose a P12 or PFX certificate file.');
        setP12CertificateStatus('Please choose a certificate file.', 'error');
        return;
    }

    const file = fileInput.files[0];
    const extension = file.name.split('.').pop()?.toLowerCase();
    if (!['p12', 'pfx'].includes(extension || '')) {
        ToastNotification.error('Certificate must be a .p12 or .pfx file.');
        setP12CertificateStatus('Certificate must be a .p12 or .pfx file.', 'error');
        return;
    }

    if (file.size > P12_CERTIFICATE_MAX_BYTES) {
        ToastNotification.error('Certificate file must not be larger than 5MB.');
        setP12CertificateStatus('Certificate file must not be larger than 5MB.', 'error');
        return;
    }

    const password = passwordInput?.value || '';
    if (!password) {
        ToastNotification.error('Certificate password is required.');
        setP12CertificateStatus('Certificate password is required.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('p12_certificate', file);
    formData.append('p12_certificate_password', password);

    setP12CertificateStatus('Saving certificate...');

    fetch('{{ url("/profile/p12-certificate") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            const message = data.errors?.p12_certificate?.[0]
                || data.errors?.p12_certificate_password?.[0]
                || data.message
                || 'Failed to save certificate.';
            throw new Error(message);
        }

        renderP12CertificateOnFile();
        fileInput.value = '';
        if (passwordInput) {
            passwordInput.value = '';
        }
        setP12CertificateStatus('Certificate saved.', 'success');
        ToastNotification.success(data.message || 'P12 certificate saved successfully!');
    })
    .catch(error => {
        setP12CertificateStatus(error.message || 'Failed to save certificate.', 'error');
        ToastNotification.error(error.message || 'Failed to save certificate.');
    });
}

function removeP12Certificate() {
    if (!confirm('Are you sure you want to remove your P12 certificate?')) {
        return;
    }

    fetch('{{ url("/profile/p12-certificate") }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderP12CertificateEmpty();
            document.getElementById('p12_certificate').value = '';
            const passwordInput = document.getElementById('p12_certificate_password');
            if (passwordInput) {
                passwordInput.value = '';
            }
            setP12CertificateStatus('Certificate removed.', 'success');
            ToastNotification.success(data.message);
        }
    })
    .catch(() => {
        ToastNotification.error('Failed to remove P12 certificate');
    });
}

// Remove E-Signature
function removeESignature() {
    if (!confirm('Are you sure you want to remove your e-signature?')) {
        return;
    }

    fetch('{{ url("/profile/e-signature") }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const preview = document.getElementById('e-signature-preview');
            if (preview) {
                preview.closest('.rounded-lg')?.remove();
            }

            const container = document.querySelector('#e_signature')?.closest('.mb-8')?.querySelector('.flex-shrink-0');
            if (container && !document.getElementById('e-signature-placeholder')) {
                container.innerHTML = `<div id="e-signature-placeholder"
                     class="w-48 h-24 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 flex items-center justify-center">
                    <div class="text-center text-gray-400 px-3">
                        <svg class="w-8 h-8 mx-auto mb-1 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        <p class="text-xs">No e-signature</p>
                    </div>
                </div>`;
            }

            const removeBtn = document.querySelector('[onclick="removeESignature()"]');
            if (removeBtn) {
                removeBtn.remove();
            }

            document.getElementById('e_signature').value = '';
            ToastNotification.success(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ToastNotification.error('Failed to remove e-signature');
    });
}

// Remove Cover Photo
function removeCoverPhoto() {
    if (confirm('Are you sure you want to remove your cover photo?')) {
        fetch('{{ url("/profile/cover") }}', {
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
    const submitButton = document.getElementById('save-profile-btn') || this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    const eSignatureInput = document.getElementById('e_signature');

    if (eSignatureInput?.files?.[0]) {
        formData.set('e_signature', eSignatureInput.files[0]);
        formData.set('e_signature_expected', '1');
    }

    // Ensure _method is set for PUT request
    if (!formData.has('_method')) {
        formData.append('_method', 'PUT');
    }

    console.log('Submitting profile form to:', this.action);
    console.log('Form data keys:', Array.from(formData.keys()));

    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...';

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // If not JSON, might be HTML error page
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned an error. Please check the console.');
            });
        }

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
