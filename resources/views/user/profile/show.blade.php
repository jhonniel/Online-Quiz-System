@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col space-y-3 min-h-0">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">My Profile</h1>
                    <p class="text-indigo-100 text-sm">Manage your profile information and photos</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url('/profile/edit') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span class="hidden sm:inline">Edit Profile</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mx-2 sm:mx-3 lg:mx-4 xl:mx-6 flex-1 overflow-hidden">
        <!-- Cover Photo Section -->
        <div class="relative h-48 sm:h-56 lg:h-64 bg-gradient-to-r from-blue-400 via-purple-500 to-pink-500">
            @if($user->hasCoverPhoto())
                <img src="{{ $user->getCoverPhotoUrl() }}"
                     alt="Cover Photo"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-r from-blue-400 via-purple-500 to-pink-500 flex items-center justify-center">
                    <div class="text-center text-white">
                        <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm opacity-75">No cover photo</p>
                    </div>
                </div>
            @endif

            <!-- Profile Picture Overlay -->
            <div class="absolute -bottom-16 left-6">
                <div class="relative">
                    @if($user->profile_picture)
                        <img src="{{ $user->getProfilePictureUrl() }}"
                             alt="{{ $user->name }}"
                             class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                    @else
                        <div class="w-32 h-32 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center">
                            <span class="text-white font-bold text-2xl">
                                {{ $user->getInitials() }}
                            </span>
                        </div>
                    @endif

                    <!-- Online Status Indicator -->
                    <div class="absolute bottom-2 right-2 w-6 h-6 bg-green-400 border-2 border-white rounded-full"></div>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="pt-20 pb-6 px-6">
            <!-- User Info -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $user->name }}</h2>
                <p class="text-gray-600 mb-4">{{ $user->email }}</p>

                @if($user->university)
                    <div class="flex items-center text-gray-500 mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="text-sm">{{ $user->university->name }}</span>
                    </div>
                @endif

                <!-- Bio Section -->
                @if($user->bio)
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">About Me</h3>
                        <p class="text-gray-700 leading-relaxed">{{ $user->bio }}</p>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="text-center text-gray-500">
                            <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <p class="text-sm">No bio added yet</p>
                        </div>
                    </div>
                @endif
            </div>

            @if($user->canAccessQrCode())
            <!-- QR Code Section -->
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-6 mb-6 border border-indigo-200">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <div class="flex-shrink-0">
                        @php
                            $user->generateQrCodeId();
                        @endphp
                        <div class="bg-white p-4 rounded-lg shadow-md border-2 border-indigo-300">
                            <div class="w-48 h-48 flex items-center justify-center">
                                {!! $user->getQrCodeSvg(200) !!}
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center justify-center sm:justify-start">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                            My QR Code
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">Share this QR code to let others easily identify you</p>
                        <div class="bg-white rounded-lg p-3 border border-gray-200 inline-block">
                            <p class="text-xs text-gray-500 mb-1">QR Code ID</p>
                            <p class="text-lg font-mono font-bold text-indigo-600">{{ $user->qr_code_id }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Stats Section -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $user->getTotalScore() }}</div>
                    <div class="text-sm text-blue-800">Total Points</div>
                </div>
                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $user->getRank() }}</div>
                    <div class="text-sm text-green-800">Current Rank</div>
                </div>
                <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $user->friends()->count() }}</div>
                    <div class="text-sm text-purple-800">Friends</div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ url('/profile/edit') }}"
                   class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Profile
                </a>
                <a href="{{ url('/user-chat') }}"
                   class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Start Chat
                </a>
            </div>
        </div>
    </div>

    <!-- Friend Requests Section -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Friend Requests
            </h3>
        </div>

        <div class="p-6">
            <!-- Search for Friends -->
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-2">
                            <h4 class="text-sm font-medium text-gray-900">Find New Friends</h4>
                            <p class="text-xs text-gray-500">Partial match on full name, email, role, department, or university</p>
                        </div>
                    </div>
                    <div class="flex-1 max-w-md">
                        <div class="relative z-30 overflow-visible">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   id="friend-search"
                                   placeholder="Search by full name, email..."
                                   class="block w-full pl-9 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                            <button type="button" id="clear-search" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                            <div id="search-results" class="absolute z-[100] w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-xl hidden max-h-80 overflow-y-auto">
                                <!-- Search results will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests -->
            @if($pendingRequests->count() > 0)
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pending Requests
                        <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ $pendingRequests->count() }}
                        </span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($pendingRequests as $request)
                            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-3 hover:shadow-md transition-all duration-200"
                                 data-friend-list-item data-search-text="{{ strtolower(trim($request->user->name.' '.$request->user->email.' '.($request->user->department?->name ?? ''))) }}">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($request->user->profile_picture)
                                            <img src="{{ $request->user->getProfilePictureUrl() }}"
                                                 alt="{{ $request->user->name }}"
                                                 class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                                        @else
                                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                                                <span class="text-white font-semibold text-sm">
                                                    {{ $request->user->getInitials() }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $request->user->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $request->user->email }}</p>
                                    </div>
                                    <div class="flex flex-col space-y-1">
                                        @if($request->status === 'accepted')
                                            <a href="{{ url('/user-chat') }}?friend={{ $request->user->id }}"
                                               class="bg-indigo-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200 text-center">
                                                Chat
                                            </a>
                                        @else
                                            <button onclick="acceptRequest({{ $request->id }})"
                                                    class="bg-green-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200">
                                                Accept
                                            </button>
                                            <button onclick="rejectRequest({{ $request->id }})"
                                                    class="bg-red-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                                                Reject
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Sent Requests -->
            @if($sentRequests->count() > 0)
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Sent Requests
                        <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ $sentRequests->count() }}
                        </span>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($sentRequests as $request)
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-3 hover:shadow-md transition-all duration-200"
                                 data-friend-list-item data-search-text="{{ strtolower(trim($request->friend->name.' '.$request->friend->email.' '.($request->friend->department?->name ?? ''))) }}">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($request->friend->profile_picture)
                                            <img src="{{ $request->friend->getProfilePictureUrl() }}"
                                                 alt="{{ $request->friend->name }}"
                                                 class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                                        @else
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                                                <span class="text-white font-semibold text-sm">
                                                    {{ $request->friend->getInitials() }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $request->friend->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $request->friend->email }}</p>
                                        <p class="text-xs {{ $request->status === 'accepted' ? 'text-green-600' : 'text-blue-600' }} font-medium">
                                            {{ $request->status === 'accepted' ? 'Accepted' : 'Pending' }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col">
                                        @if($request->status === 'accepted')
                                            <a href="{{ url('/user-chat') }}?friend={{ $request->friend->id }}"
                                               class="bg-indigo-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200 text-center">
                                                Chat
                                            </a>
                                        @else
                                            <button onclick="cancelRequest({{ $request->id }})"
                                                    class="bg-red-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                                                Cancel
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Friends List -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    My Friends
                    <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded-full">
                        {{ $allFriends->count() }}
                    </span>
                </h4>
                @if($allFriends->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($allFriends as $friend)
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-3 hover:shadow-md transition-all duration-200 group"
                                 data-friend-list-item data-search-text="{{ strtolower(trim($friend->name.' '.$friend->email.' '.($friend->department?->name ?? ''))) }}">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($friend->profile_picture)
                                            <img src="{{ $friend->getProfilePictureUrl() }}"
                                                 alt="{{ $friend->name }}"
                                                 class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                                        @else
                                            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                                                <span class="text-white font-semibold text-sm">
                                                    {{ $friend->getInitials() }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $friend->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $friend->email }}</p>
                                        <div class="flex items-center mt-1">
                                            <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                                            <span class="text-xs text-gray-500">Online</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col space-y-1">
                                        <a href="{{ url('/user-chat') }}?friend={{ $friend->id }}"
                                           class="bg-indigo-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200">
                                            Chat
                                        </a>
                                        <button onclick="removeFriend({{ $friend->id }})"
                                                class="bg-red-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                                            Unfriend
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-20 h-20 mx-auto bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No friends yet</h3>
                        <p class="text-xs text-gray-500 mb-4">Start building your network by searching for users above.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<x-toast />

<!-- Loading Overlay -->
<div id="loading-overlay" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                <svg class="animate-spin h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Processing...</h3>
            <p class="text-sm text-gray-500 mt-1">Please wait while we process your request.</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let searchTimeout;
    const friendSearchUrl = @json(route('friends.search'));

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function filterFriendListCards(query) {
        const needle = query.trim().toLowerCase();
        document.querySelectorAll('[data-friend-list-item]').forEach((card) => {
            const haystack = (card.getAttribute('data-search-text') || card.textContent || '').toLowerCase();
            const show = needle === '' || haystack.includes(needle);
            card.classList.toggle('hidden', !show);
        });
    }

    function renderSearchResults(users, resultsDiv) {
        if (!Array.isArray(users) || users.length === 0) {
            resultsDiv.innerHTML = `
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">No users found matching your search</p>
                    <p class="text-gray-400 text-xs mt-1">Try part of a full name, email, role, department, or university</p>
                </div>
            `;
            return;
        }

        resultsDiv.innerHTML = `
            <div class="p-2">
                <div class="text-xs text-gray-500 px-3 py-2 border-b border-gray-100">
                    Found ${users.length} user${users.length === 1 ? '' : 's'}
                </div>
                ${users.map(user => {
                    const rawName = user.full_name || user.name || '';
                    const name = escapeHtml(rawName);
                    const email = escapeHtml(user.email);
                    const university = user.university ? escapeHtml(String(user.university)) : '';
                    const safeAttrName = rawName.replace(/"/g, '&quot;');
                    const avatar = user.profile_picture_url
                        ? `<img src="${escapeHtml(user.profile_picture_url)}" alt="${name}" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 shadow-sm">`
                        : `<div class="w-12 h-12 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full flex items-center justify-center border-2 border-gray-200 shadow-sm">
                                <span class="text-white font-semibold text-lg">${escapeHtml(rawName.charAt(0).toUpperCase())}</span>
                           </div>`;
                    let action = '';
                    if (user.friendship_status === 'accepted') {
                        action = `<a href="{{ url('/user-chat') }}?friend=${user.id}" class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-indigo-500 text-white hover:bg-indigo-600">Chat</a>`;
                    } else if (user.friendship_status === 'pending') {
                        action = `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getStatusClass(user.friendship_status)}">${getStatusText(user.friendship_status)}</span>`;
                    } else {
                        action = `<button type="button" class="js-add-friend inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-indigo-500 text-white hover:bg-indigo-600" data-user-id="${user.id}" data-user-name="${safeAttrName}">Add Friend</button>`;
                    }
                    return `
                        <div class="p-3 hover:bg-indigo-50 border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">${avatar}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] uppercase tracking-wide text-gray-400">Full name</p>
                                    <p class="font-semibold text-gray-900 truncate text-sm sm:text-base">${name}</p>
                                    <p class="text-xs sm:text-sm text-gray-500 truncate">${email}</p>
                                    ${user.department ? `<p class="text-xs text-gray-400 truncate">${escapeHtml(String(user.department))}</p>` : ''}
                                    ${university ? `<p class="text-xs text-gray-400 truncate">${university}</p>` : ''}
                                </div>
                                <div class="flex-shrink-0">${action}</div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;

        resultsDiv.querySelectorAll('.js-add-friend').forEach((btn) => {
            btn.addEventListener('click', () => {
                selectUser(
                    parseInt(btn.dataset.userId, 10),
                    btn.getAttribute('data-user-name') || '',
                    '',
                    'none',
                    null
                );
            });
        });
    }

    // Clear search functionality
    document.getElementById('clear-search').addEventListener('click', function() {
        const searchInput = document.getElementById('friend-search');
        const resultsDiv = document.getElementById('search-results');
        const clearButton = document.getElementById('clear-search');

        searchInput.value = '';
        resultsDiv.classList.add('hidden');
        clearButton.classList.add('hidden');
        filterFriendListCards('');
    });

    // Show/hide clear button based on input
    document.getElementById('friend-search').addEventListener('input', function(e) {
        const clearButton = document.getElementById('clear-search');
        if (e.target.value.length > 0) {
            clearButton.classList.remove('hidden');
        } else {
            clearButton.classList.add('hidden');
        }
    });

    // Search functionality (partial match + filter lists on this page)
    document.getElementById('friend-search').addEventListener('input', function(e) {
        const query = e.target.value;
        const resultsDiv = document.getElementById('search-results');

        clearTimeout(searchTimeout);
        filterFriendListCards(query);

        if (query.trim().length < 1) {
            resultsDiv.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            resultsDiv.innerHTML = '<p class="text-sm text-gray-500 py-4 text-center">Loading…</p>';
            resultsDiv.classList.remove('hidden');

            fetch(`${friendSearchUrl}?q=${encodeURIComponent(query.trim())}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
            .then(async (response) => {
                const data = await response.json().catch(() => null);
                if (!response.ok) {
                    throw new Error((data && data.message) ? data.message : 'Search request failed');
                }
                return Array.isArray(data) ? data : [];
            })
            .then(users => {
                renderSearchResults(users, resultsDiv);
                resultsDiv.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Search error:', error);
                resultsDiv.innerHTML = `
                    <div class="p-4 text-center">
                        <p class="text-red-500 text-sm">Error searching users</p>
                        <p class="text-gray-400 text-xs mt-1">Please try again</p>
                    </div>
                `;
                resultsDiv.classList.remove('hidden');
            });
        }, 250);
    });

    function getStatusClass(status) {
        switch(status) {
            case 'accepted': return 'bg-green-100 text-green-800';
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'blocked': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    function getStatusText(status) {
        switch(status) {
            case 'accepted': return 'Friends';
            case 'pending': return 'Pending';
            case 'blocked': return 'Blocked';
            default: return 'Add Friend';
        }
    }

    function selectUser(userId, name, email, status, friendshipId) {
        if (status === 'none') {
            sendFriendRequest(userId, name);
        } else if (status === 'pending') {
            showNotification('Friend request already sent', 'warning');
        } else if (status === 'accepted') {
            // Navigate to chat with the friend
            window.location.href = '{{ url("/user-chat") }}?friend=' + userId;
        } else if (status === 'blocked') {
            showNotification('User is blocked', 'error');
        }
        document.getElementById('search-results').classList.add('hidden');
        document.getElementById('friend-search').value = '';
    }

    function sendFriendRequest(userId, userName) {
        showLoading();

        fetch('{{ url("/friends/send-request") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ friend_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification(`Friend request sent to ${userName}!`, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.error || 'Error sending friend request', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showNotification('Error sending friend request', 'error');
        });
    }

    function acceptRequest(friendshipId) {
        showLoading();

            fetch(`{{ url('friends') }}/${friendshipId}/accept`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification('Friend request accepted!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.error || 'Error accepting friend request', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showNotification('Error accepting friend request', 'error');
        });
    }

    function rejectRequest(friendshipId) {
        if (confirm('Are you sure you want to reject this friend request?')) {
            showLoading();

            fetch(`{{ url('friends') }}/${friendshipId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showNotification('Friend request rejected', 'info');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.error || 'Error rejecting friend request', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showNotification('Error rejecting friend request', 'error');
            });
        }
    }

    function cancelRequest(friendshipId) {
        if (confirm('Are you sure you want to cancel this friend request?')) {
            showLoading();

            fetch(`{{ url('friends') }}/${friendshipId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showNotification('Friend request cancelled', 'info');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.error || 'Error cancelling friend request', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showNotification('Error cancelling friend request', 'error');
            });
        }
    }

    function removeFriend(friendshipId) {
        if (confirm('Are you sure you want to unfriend this user?')) {
            showLoading();

            fetch(`{{ url('friends') }}/${friendshipId}/remove`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showNotification('User unfriended successfully', 'info');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.error || 'Error unfriending user', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showNotification('Error unfriending user', 'error');
            });
        }
    }

    function showLoading() {
        document.getElementById('loading-overlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loading-overlay').classList.add('hidden');
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${getNotificationClass(type)}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${getNotificationIcon(type)}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    function getNotificationClass(type) {
        switch(type) {
            case 'success': return 'bg-green-500 text-white';
            case 'error': return 'bg-red-500 text-white';
            case 'warning': return 'bg-yellow-500 text-white';
            default: return 'bg-blue-500 text-white';
        }
    }

    function getNotificationIcon(type) {
        switch(type) {
            case 'success': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            case 'error': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            case 'warning': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>';
            default: return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        }
    }

    // Hide search results when clicking outside
    document.addEventListener('click', function(e) {
        const searchResults = document.getElementById('search-results');
        const searchInput = document.getElementById('friend-search');
        if (!searchResults.contains(e.target) && !searchInput.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });
</script>
@endsection
