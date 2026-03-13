@extends('layouts.user')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Files</h1>
                <p class="mt-1 text-sm text-gray-600">View and manage your personal and shared files.</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="document.getElementById('user-create-folder-modal').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Folder
                </button>
                <button onclick="document.getElementById('user-upload-file-modal').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Upload File
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
            <div class="mb-4 flex items-center space-x-2 text-sm">
                <a href="{{ url('/files') }}" class="text-indigo-600 hover:text-indigo-800">Home</a>
                @foreach($breadcrumbs as $breadcrumb)
                    <span class="text-gray-400">/</span>
                    <a href="{{ url('/files?folder_id=' . $breadcrumb->id) }}" class="text-indigo-600 hover:text-indigo-800">{{ $breadcrumb->name }}</a>
                @endforeach
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            @if($files->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4">
                    @foreach($files as $item)
                        <div class="group relative bg-gray-50 rounded-lg border border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all duration-200 p-3">
                            @if($item->isFolder())
                                <a href="{{ url('/files?folder_id=' . $item->id) }}" class="block text-center">
                                    <div class="flex justify-center mb-2">
                                        <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-medium text-gray-900 truncate" title="{{ $item->name }}">{{ $item->name }}</h3>
                                    <p class="text-xs text-gray-500 mt-1">Folder</p>
                                </a>
                            @else
                                <div class="text-center">
                                    @php
                                        $isImage = $item->mime_type && str_starts_with($item->mime_type, 'image/');
                                        $isVideo = $item->mime_type && str_starts_with($item->mime_type, 'video/');
                                        $thumbnailUrl = null;

                                        if ($isImage) {
                                            $thumbnailUrl = url('/files/' . $item->id . '/view');
                                        }
                                    @endphp

                                    <div class="flex justify-center mb-2 h-20 overflow-hidden rounded bg-gray-100">
                                        @if($thumbnailUrl)
                                            <img src="{{ $thumbnailUrl }}" alt="{{ $item->name }}"
                                                 class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                                 onclick="openUserPreviewModal('{{ $item->id }}', '{{ addslashes($item->name) }}', '{{ $item->mime_type }}', '{{ url('/files/' . $item->id . '/view') }}')">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                @if($isVideo)
                                                    <svg class="w-10 h-10 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                                                    </svg>
                                                @elseif(str_starts_with($item->mime_type ?? '', 'application/pdf'))
                                                    <svg class="w-10 h-10 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <h3 class="text-sm font-medium text-gray-900 truncate" title="{{ $item->name }}">{{ $item->name }}</h3>
                                    <p class="text-xs text-gray-500 mt-1">{{ $item->formatted_size }}</p>
                                </div>
                            @endif

                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="p-1 rounded-md hover:bg-gray-200 text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak
                                         class="absolute right-0 mt-2 w-44 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                        <div class="py-1">
                                            @if($item->isFile())
                                                <button onclick="openUserPreviewModal('{{ $item->id }}', '{{ addslashes($item->name) }}', '{{ $item->mime_type }}', '{{ url('/files/' . $item->id . '/view') }}')"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Preview</button>
                                                <a href="{{ url('/files/' . $item->id . '/download') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Download</a>
                                            @elseif($item->isFolder())
                                                <a href="{{ url('/files?folder_id=' . $item->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Open</a>
                                            @endif
                                            @if($item->uploaded_by === auth()->id())
                                                <button type="button" onclick="openUserShareModal({{ $item->id }}, '{{ $item->type }}')"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Share</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $files->appends(request()->query())->links() }}
                </div>
            @else
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No files or folders</h3>
                    <p class="mt-1 text-sm text-gray-500">Start by uploading a file or creating a folder.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="user-upload-file-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Upload File</h3>
                    <button onclick="document.getElementById('user-upload-file-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <!-- Upload Progress (Hidden by default) -->
                <div id="user-upload-progress-container" class="hidden mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Uploading...</span>
                        <span id="user-upload-percentage" class="text-sm font-medium text-indigo-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="user-upload-progress-bar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                    <p id="user-upload-status" class="text-xs text-gray-500 mt-2">Preparing upload...</p>
                </div>

                <form id="user-upload-file-form" action="{{ url('/files') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? null }}">
                    <div class="mb-4">
                        <label for="user-file" class="block text-sm font-medium text-gray-700 mb-2">Select File or Video (Max: 5GB)</label>
                        <input type="file" name="file" id="user-file" required accept="*/*"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">You can upload documents, images, and videos. Maximum file size: 5GB.</p>
                    </div>
                    <div class="mb-4">
                        <label for="user-description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" id="user-description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div id="user-upload-form-buttons" class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('user-upload-file-modal').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                        <button type="submit" id="user-upload-submit-btn"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div id="user-create-folder-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Create Folder</h3>
                    <button onclick="document.getElementById('user-create-folder-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="{{ url('/files/create-folder') }}" method="POST">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
                    <div class="mb-4">
                        <label for="user-folder-name" class="block text-sm font-medium text-gray-700 mb-2">Folder Name</label>
                        <input type="text" name="name" id="user-folder-name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label for="user-folder-description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" id="user-folder-description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('user-create-folder-modal').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="user-share-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Share <span id="user-share-item-type"></span></h3>
                    <button type="button" onclick="document.getElementById('user-share-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="user-share-form" method="POST" action="">
                    @csrf
                    <div class="mb-4">
                        <label for="user-share_user_id" class="block text-sm font-medium text-gray-700 mb-2">Add user to folder (students &amp; employees)</label>
                        <select name="user_id" id="user-share_user_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Choose a user...</option>
                            @foreach($users ?? [] as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) — {{ ucfirst($u->role ?? '') }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">You can share this folder with other students and employees.</p>
                    </div>
                    <div class="mb-4 space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="can_view" value="1" checked
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Can view</span>
                        </label>
                        <label class="flex items-center" id="user-can-upload-container">
                            <input type="checkbox" name="can_upload" value="1"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Can upload (folders only)</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('user-share-modal').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Share
                        </button>
                    </div>
                </form>
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-gray-900">Shared with</h4>
                        <button type="button" class="text-xs text-indigo-600 hover:text-indigo-800 underline"
                                onclick="refreshUserSharedUsers()">
                            Refresh
                        </button>
                    </div>
                    <div id="user-shared-users-loading" class="text-sm text-gray-500">Loading...</div>
                    <div id="user-shared-users-empty" class="hidden text-sm text-gray-500">Not shared with anyone yet.</div>
                    <div id="user-shared-users-error" class="hidden text-sm text-red-600"></div>
                    <ul id="user-shared-users-list" class="hidden divide-y divide-gray-200 max-h-56 overflow-y-auto"></ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="user-preview-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50">
        <div class="relative top-4 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white mb-4">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 id="user-preview-file-name" class="text-lg font-medium text-gray-900"></h3>
                    <div class="flex items-center space-x-2">
                        <a id="user-preview-download-url" href="#" download
                           class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download
                        </a>
                        <button onclick="document.getElementById('user-preview-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="user-preview-content" class="bg-gray-50 rounded-lg p-4 min-h-[300px] flex items-center justify-center">
                    <!-- Content inserted via JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Fixed lower-left upload progress indicator (visible during upload) -->
    <div id="user-upload-floating-indicator" class="hidden fixed bottom-4 left-4 z-[9999] w-72 rounded-lg border border-gray-200 bg-white shadow-lg p-4">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-indigo-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <span id="user-upload-floating-title" class="text-sm font-medium text-gray-900">Uploading...</span>
        </div>
        <div class="flex justify-between text-xs text-gray-600 mb-1">
            <span id="user-upload-floating-percent">0%</span>
            <span id="user-upload-floating-remaining">—</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div id="user-upload-floating-bar" class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <p id="user-upload-floating-detail" class="text-xs text-gray-500 mt-2 truncate">Preparing...</p>
    </div>

    <script>
        const USER_FILES_DOWNLOAD_URL = @json(url('/files/__FILE__/download'));
        const USER_FILES_PRESIGN_URL = @json(url('/files/presign'));
        const USER_FILES_CONFIRM_URL = @json(url('/files/confirm'));
        const USER_FILES_MULTIPART_INITIATE_URL = @json(url('/files/multipart/initiate'));
        const USER_FILES_MULTIPART_PRESIGN_CHUNK_URL = @json(url('/files/multipart/presign-chunk'));
        const USER_FILES_MULTIPART_COMPLETE_URL = @json(url('/files/multipart/complete'));
        const USER_FILES_MULTIPART_ABORT_URL = @json(url('/files/multipart/abort'));
        const USER_FILES_SHARE_URL = @json(url('/files/__FILE__/share'));
        const USER_FILES_UNSHARE_URL = @json(url('/files/__FILE__/unshare'));
        const USER_FILES_SHARED_USERS_URL = @json(url('/files/__FILE__/shared-users'));

        let currentUserShareItemId = null;

        function setUserSharedUsersState(state) {
            const loading = document.getElementById('user-shared-users-loading');
            const empty = document.getElementById('user-shared-users-empty');
            const errEl = document.getElementById('user-shared-users-error');
            const list = document.getElementById('user-shared-users-list');
            loading.classList.add('hidden');
            empty.classList.add('hidden');
            errEl.classList.add('hidden');
            list.classList.add('hidden');
            if (state === 'loading') loading.classList.remove('hidden');
            if (state === 'empty') empty.classList.remove('hidden');
            if (state === 'error') errEl.classList.remove('hidden');
            if (state === 'list') list.classList.remove('hidden');
        }

        function renderUserSharedUsers(users) {
            const list = document.getElementById('user-shared-users-list');
            list.innerHTML = '';
            users.forEach(u => {
                const canView = !!(u.pivot && u.pivot.can_view);
                const canUpload = !!(u.pivot && u.pivot.can_upload);
                const li = document.createElement('li');
                li.className = 'py-2 flex items-center justify-between';
                const name = String(u.name ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                const email = String(u.email ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                li.innerHTML = `
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-gray-900 truncate">${name}</div>
                        <div class="text-xs text-gray-500 truncate">${email}</div>
                        <div class="mt-1 flex flex-wrap gap-1">
                            ${canView ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-green-100 text-green-800">View</span>' : ''}
                            ${canUpload ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-indigo-100 text-indigo-800">Upload</span>' : ''}
                        </div>
                    </div>
                    <div class="flex-shrink-0 pl-2">
                        <button type="button" class="text-xs text-red-600 hover:text-red-800 underline user-unshare-btn" data-user-id="${u.id}">Remove</button>
                    </div>
                `;
                list.appendChild(li);
            });
            list.querySelectorAll('.user-unshare-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const userId = btn.getAttribute('data-user-id');
                    if (currentUserShareItemId && userId && confirm('Remove this user\'s access?')) {
                        unshareUserFromFile(currentUserShareItemId, userId);
                    }
                });
            });
        }

        function loadUserSharedUsers(fileId) {
            currentUserShareItemId = fileId;
            setUserSharedUsersState('loading');
            document.getElementById('user-shared-users-error').textContent = '';
            const url = USER_FILES_SHARED_USERS_URL.replace('__FILE__', fileId);
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(async (res) => {
                    const data = await res.json().catch(() => null);
                    if (!res.ok) throw new Error((data && data.message) ? data.message : 'Failed to load.');
                    return data;
                })
                .then(users => {
                    if (!Array.isArray(users) || users.length === 0) {
                        setUserSharedUsersState('empty');
                        return;
                    }
                    renderUserSharedUsers(users);
                    setUserSharedUsersState('list');
                })
                .catch(err => {
                    document.getElementById('user-shared-users-error').textContent = err && err.message ? err.message : 'Failed to load.';
                    setUserSharedUsersState('error');
                });
        }

        function refreshUserSharedUsers() {
            if (currentUserShareItemId) loadUserSharedUsers(currentUserShareItemId);
        }

        function unshareUserFromFile(fileId, userId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch(USER_FILES_UNSHARE_URL.replace('__FILE__', fileId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ user_id: userId }),
            })
            .then(res => res.json().catch(() => ({})))
            .then(() => loadUserSharedUsers(fileId))
            .catch(() => alert('Failed to remove sharing.'));
        }

        function openUserShareModal(id, type) {
            document.getElementById('user-share-item-type').textContent = type === 'folder' ? 'Folder' : 'File';
            document.getElementById('user-share-form').action = USER_FILES_SHARE_URL.replace('__FILE__', id);
            const canUploadContainer = document.getElementById('user-can-upload-container');
            canUploadContainer.style.display = type === 'folder' ? 'flex' : 'none';
            if (type !== 'folder') document.querySelector('#user-share-form input[name="can_upload"]').checked = false;
            document.getElementById('user-share_user_id').value = '';
            document.querySelector('#user-share-form input[name="can_view"]').checked = true;
            document.getElementById('user-share-modal').classList.remove('hidden');
            loadUserSharedUsers(id);
        }

        function openUserPreviewModal(id, name, mimeType, url) {
            document.getElementById('user-preview-file-name').textContent = name;
            document.getElementById('user-preview-download-url').href = USER_FILES_DOWNLOAD_URL.replace('__FILE__', id);

            const previewContent = document.getElementById('user-preview-content');
            previewContent.innerHTML = '';

            if (mimeType && mimeType.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = url;
                img.className = 'max-w-full max-h-[70vh] mx-auto rounded-lg';
                img.alt = name;
                previewContent.appendChild(img);
            } else if (mimeType && mimeType === 'application/pdf') {
                const iframe = document.createElement('iframe');
                iframe.src = url;
                iframe.className = 'w-full h-[70vh] border-0 rounded-lg';
                previewContent.appendChild(iframe);
            } else if (mimeType && mimeType.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = url;
                video.controls = true;
                video.controlsList = 'nodownload';
                video.preload = 'metadata';
                video.playsInline = true;
                video.className = 'max-w-full max-h-[70vh] mx-auto rounded-lg bg-black';
                previewContent.appendChild(video);
            } else if (mimeType && mimeType.startsWith('audio/')) {
                const audio = document.createElement('audio');
                audio.src = url;
                audio.controls = true;
                audio.className = 'w-full mx-auto';
                previewContent.appendChild(audio);
            } else {
                previewContent.innerHTML = '<div class="text-center py-8"><p class="text-gray-500">Preview not available for this file type.</p><p class="text-sm text-gray-400 mt-2">Use the download button to open the file.</p></div>';
            }

            document.getElementById('user-preview-modal').classList.remove('hidden');
        }

        // Direct upload to Spaces with progress
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('user-upload-file-form');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(form);
                const fileInput = document.getElementById('user-file');
                const file = fileInput?.files?.[0];

                if (!file) {
                    alert('Please select a file to upload.');
                    return;
                }

                const maxSize = 5368709120; // 5GB in bytes
                if (file.size > maxSize) {
                    alert('File size exceeds 5GB limit. Please select a smaller file.');
                    return;
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const folderId = formData.get('folder_id') || null;
                const description = formData.get('description') || '';

                // Show progress UI (modal + floating lower-left indicator)
                document.getElementById('user-upload-progress-container').classList.remove('hidden');
                document.getElementById('user-upload-form-buttons').style.display = 'none';
                document.getElementById('user-upload-status').textContent = 'Preparing upload for ' + file.name + '...';

                const totalBytes = file.size;
                const totalMB = (totalBytes / 1048576).toFixed(2);
                function showFloatingIndicator(show) {
                    const el = document.getElementById('user-upload-floating-indicator');
                    if (show) el.classList.remove('hidden'); else el.classList.add('hidden');
                }
                function updateFloatingIndicator(percent, uploadedBytes, totalBytes, detail) {
                    const el = document.getElementById('user-upload-floating-indicator');
                    el.classList.remove('hidden');
                    document.getElementById('user-upload-floating-percent').textContent = Math.round(percent) + '%';
                    document.getElementById('user-upload-floating-bar').style.width = percent + '%';
                    const upMB = (uploadedBytes / 1048576).toFixed(2);
                    const totMB = (totalBytes / 1048576).toFixed(2);
                    const remMB = ((totalBytes - uploadedBytes) / 1048576).toFixed(2);
                    document.getElementById('user-upload-floating-remaining').textContent = uploadedBytes >= totalBytes ? 'Done' : remMB + ' MB remaining';
                    document.getElementById('user-upload-floating-detail').textContent = detail || (upMB + ' MB of ' + totMB + ' MB');
                }

                showFloatingIndicator(true);
                document.getElementById('user-upload-floating-title').textContent = file.name.length > 28 ? file.name.slice(0, 25) + '...' : file.name;
                updateFloatingIndicator(0, 0, totalBytes, 'Preparing...');

                // Use chunked upload for videos or files larger than 10MB
                const CHUNK_SIZE = 10 * 1024 * 1024; // 10MB
                const isVideo = file.type && file.type.startsWith('video/');
                const useChunked = file.size > CHUNK_SIZE || isVideo;

                function hideFloatingIndicator() {
                    document.getElementById('user-upload-floating-indicator').classList.add('hidden');
                }
                function fallbackToDirectUpload() {
                    document.getElementById('user-upload-status').textContent = 'Uploading file...';
                    updateFloatingIndicator(0, 0, totalBytes, 'Uploading (no progress available)...');
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(async (res) => {
                        if (res.ok) {
                            updateFloatingIndicator(100, totalBytes, totalBytes, 'Saved! Reloading...');
                            document.getElementById('user-upload-progress-bar').style.width = '100%';
                            document.getElementById('user-upload-percentage').textContent = '100%';
                            document.getElementById('user-upload-status').textContent = 'Saved! Reloading...';
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            const text = await res.text();
                            let msg = 'Upload failed.';
                            try {
                                const data = JSON.parse(text);
                                if (data && data.message) msg = data.message;
                            } catch (_) {
                                if (text) msg = text;
                            }
                            throw new Error(msg);
                        }
                    })
                    .catch((err) => {
                        hideFloatingIndicator();
                        document.getElementById('user-upload-status').textContent = 'Error: ' + (err?.message || 'Upload failed.');
                        document.getElementById('user-upload-progress-bar').classList.remove('bg-indigo-600');
                        document.getElementById('user-upload-progress-bar').classList.add('bg-red-600');
                        document.getElementById('user-upload-form-buttons').style.display = 'flex';
                    });
                }

                if (useChunked) {
                    // Chunked multipart upload
                    let uploadId = null;
                    let path = null;
                    let chunkSize = CHUNK_SIZE;
                    const totalChunks = Math.ceil(file.size / chunkSize);
                    const uploadedParts = [];

                    // Step 1: Initiate multipart upload
                    fetch(USER_FILES_MULTIPART_INITIATE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            original_name: file.name,
                            mime_type: file.type || null,
                            size: file.size,
                            folder_id: folderId,
                        }),
                    })
                    .then(async (res) => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            const msg = (data.message || res.statusText || '').toLowerCase();
                            if (res.status === 500 || res.status === 503 || msg.includes('not configured') || msg.includes('spaces')) {
                                fallbackToDirectUpload();
                                return { _fallback: true };
                            }
                            throw new Error(data.message || 'Failed to initiate multipart upload.');
                        }
                        return data;
                    })
                    .then((initData) => {
                        if (initData && initData._fallback) return;
                        uploadId = initData.upload_id;
                        path = initData.path;
                        chunkSize = initData.chunk_size || CHUNK_SIZE;

                        // Step 2: Upload chunks sequentially
                        let currentChunk = 0;
                        const uploadChunk = (chunkIndex) => {
                            if (chunkIndex >= totalChunks) {
                                // All chunks uploaded, complete multipart upload
                                document.getElementById('user-upload-status').textContent = 'Completing upload...';
                                updateFloatingIndicator(99, file.size, file.size, 'Completing upload...');

                                fetch(USER_FILES_MULTIPART_COMPLETE_URL, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    body: JSON.stringify({
                                        upload_id: uploadId,
                                        path: path,
                                        parts: uploadedParts,
                                        original_name: file.name,
                                        mime_type: file.type || null,
                                        size: file.size,
                                        folder_id: folderId,
                                        description: description,
                                    }),
                                })
                                .then(async (res) => {
                                    const data = await res.json().catch(() => ({}));
                                    if (!res.ok) throw new Error(data.message || 'Failed to complete upload.');
                                    return data;
                                })
                                .then(() => {
                                    updateFloatingIndicator(100, file.size, file.size, 'Saved! Reloading...');
                                    document.getElementById('user-upload-progress-bar').style.width = '100%';
                                    document.getElementById('user-upload-percentage').textContent = '100%';
                                    document.getElementById('user-upload-status').textContent = 'Saved! Reloading...';
                                    setTimeout(() => window.location.reload(), 800);
                                })
                                .catch((err) => {
                                    hideFloatingIndicator();
                                    document.getElementById('user-upload-status').textContent = 'Error: ' + (err?.message || 'Failed to complete upload.');
                                    document.getElementById('user-upload-progress-bar').classList.remove('bg-indigo-600');
                                    document.getElementById('user-upload-progress-bar').classList.add('bg-red-600');
                                    document.getElementById('user-upload-form-buttons').style.display = 'flex';
                                });
                                return;
                            }

                            const start = chunkIndex * chunkSize;
                            const end = Math.min(start + chunkSize, file.size);
                            const chunk = file.slice(start, end);
                            const partNumber = chunkIndex + 1;

                            // Get presigned URL for this chunk
                            fetch(USER_FILES_MULTIPART_PRESIGN_CHUNK_URL, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({
                                    upload_id: uploadId,
                                    path: path,
                                    part_number: partNumber,
                                }),
                            })
                            .then(async (res) => {
                                const data = await res.json().catch(() => ({}));
                                if (!res.ok) throw new Error(data.message || 'Failed to get presigned URL for chunk.');
                                return data;
                            })
                            .then((presign) => {
                                // Upload chunk
                                const chunkXhr = new XMLHttpRequest();

                                chunkXhr.addEventListener('load', function () {
                                    if (chunkXhr.status >= 200 && chunkXhr.status < 300) {
                                        const etag = chunkXhr.getResponseHeader('ETag') || chunkXhr.getResponseHeader('etag');
                                        if (!etag) {
                                            throw new Error('Missing ETag in chunk response.');
                                        }

                                        uploadedParts.push({
                                            part_number: partNumber,
                                            etag: etag.replace(/"/g, ''), // Remove quotes from ETag
                                        });

                                        // Update progress (modal + floating indicator)
                                        const uploadedSoFar = Math.min((chunkIndex + 1) * chunkSize, file.size);
                                        const overallProgress = (uploadedSoFar / file.size) * 100;
                                        document.getElementById('user-upload-progress-bar').style.width = overallProgress + '%';
                                        document.getElementById('user-upload-percentage').textContent = Math.round(overallProgress) + '%';
                                        const uploadedMB = (uploadedSoFar / 1048576).toFixed(2);
                                        const totMB = (file.size / 1048576).toFixed(2);
                                        const remMB = ((file.size - uploadedSoFar) / 1048576).toFixed(2);
                                        document.getElementById('user-upload-status').textContent =
                                            `Chunk ${partNumber}/${totalChunks}: ${uploadedMB} MB / ${totMB} MB`;
                                        updateFloatingIndicator(overallProgress, uploadedSoFar, file.size,
                                            `${uploadedMB} MB of ${totMB} MB • ${remMB} MB remaining`);

                                        // Upload next chunk
                                        uploadChunk(chunkIndex + 1);
                                    } else {
                                        throw new Error('Chunk upload failed (HTTP ' + chunkXhr.status + ').');
                                    }
                                });

                                chunkXhr.addEventListener('error', function () {
                                    throw new Error('Chunk upload failed.');
                                });

                                chunkXhr.open('PUT', presign.upload_url);
                                if (presign.headers) {
                                    Object.keys(presign.headers).forEach((key) => {
                                        const lower = String(key).toLowerCase();
                                        if (lower === 'host' || lower === 'content-length') return;
                                        try {
                                            chunkXhr.setRequestHeader(key, presign.headers[key]);
                                        } catch (e) {
                                            // ignore headers the browser disallows
                                        }
                                    });
                                }

                                chunkXhr.send(chunk);
                            })
                            .catch((err) => {
                                hideFloatingIndicator();
                                document.getElementById('user-upload-status').textContent = 'Error: ' + (err?.message || 'Chunk upload failed.');
                                document.getElementById('user-upload-progress-bar').classList.remove('bg-indigo-600');
                                document.getElementById('user-upload-progress-bar').classList.add('bg-red-600');
                                document.getElementById('user-upload-form-buttons').style.display = 'flex';
                            });
                        };

                        // Start uploading chunks
                        uploadChunk(0);
                    })
                    .catch((err) => {
                        hideFloatingIndicator();
                        document.getElementById('user-upload-status').textContent = 'Error: ' + (err?.message || 'Failed to initiate upload.');
                        document.getElementById('user-upload-progress-bar').classList.remove('bg-indigo-600');
                        document.getElementById('user-upload-progress-bar').classList.add('bg-red-600');
                        document.getElementById('user-upload-form-buttons').style.display = 'flex';
                    });
                } else {
                    // Single PUT upload for smaller files (original method)
                    fetch(USER_FILES_PRESIGN_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            original_name: file.name,
                            mime_type: file.type || null,
                            size: file.size,
                            folder_id: folderId,
                        }),
                    })
                    .then(async (res) => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            const msg = (data.message || res.statusText || '').toLowerCase();
                            if (res.status === 500 || res.status === 503 || msg.includes('not configured') || msg.includes('spaces')) {
                                fallbackToDirectUpload();
                                return { _fallback: true };
                            }
                            throw new Error(data.message || 'Failed to prepare upload.');
                        }
                        return data;
                    })
                    .then((presign) => {
                        if (presign && presign._fallback) return;
                        const xhr = new XMLHttpRequest();

                        xhr.upload.addEventListener('progress', function (e) {
                            if (e.lengthComputable) {
                                const percent = (e.loaded / e.total) * 100;
                                const rounded = Math.round(percent);
                                document.getElementById('user-upload-progress-bar').style.width = percent + '%';
                                document.getElementById('user-upload-percentage').textContent = rounded + '%';
                                document.getElementById('user-upload-status').textContent = `Uploading to Spaces... (${rounded}%)`;
                                const upMB = (e.loaded / 1048576).toFixed(2);
                                const totMB = (e.total / 1048576).toFixed(2);
                                const remMB = ((e.total - e.loaded) / 1048576).toFixed(2);
                                updateFloatingIndicator(percent, e.loaded, e.total, upMB + ' MB of ' + totMB + ' MB • ' + remMB + ' MB remaining');
                            }
                        });

                        xhr.addEventListener('load', function () {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                document.getElementById('user-upload-status').textContent = 'Upload complete! Saving record...';
                                updateFloatingIndicator(100, file.size, file.size, 'Saving record...');

                                fetch(USER_FILES_CONFIRM_URL, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    body: JSON.stringify({
                                        path: presign.path,
                                        original_name: file.name,
                                        mime_type: file.type || null,
                                        size: file.size,
                                        folder_id: folderId,
                                        description: description,
                                    }),
                                })
                                .then(async (res) => {
                                    const data = await res.json().catch(() => ({}));
                                    if (!res.ok) throw new Error(data.message || 'Failed to save uploaded file.');
                                    return data;
                                })
                                .then(() => {
                                    updateFloatingIndicator(100, file.size, file.size, 'Saved! Reloading...');
                                    document.getElementById('user-upload-progress-bar').style.width = '100%';
                                    document.getElementById('user-upload-percentage').textContent = '100%';
                                    document.getElementById('user-upload-status').textContent = 'Saved! Reloading...';
                                    setTimeout(() => window.location.reload(), 800);
                                })
                                .catch((err) => {
                                    hideFloatingIndicator();
                                    document.getElementById('user-upload-status').textContent = 'Error: ' + (err?.message || 'Failed to save file.');
                                    document.getElementById('user-upload-progress-bar').classList.remove('bg-indigo-600');
                                    document.getElementById('user-upload-progress-bar').classList.add('bg-red-600');
                                    document.getElementById('user-upload-form-buttons').style.display = 'flex';
                                });
                            } else {
                                hideFloatingIndicator();
                                document.getElementById('user-upload-status').textContent = 'Error: Upload to Spaces failed (HTTP ' + xhr.status + ').';
                                document.getElementById('user-upload-progress-bar').classList.remove('bg-indigo-600');
                                document.getElementById('user-upload-progress-bar').classList.add('bg-red-600');
                                document.getElementById('user-upload-form-buttons').style.display = 'flex';
                            }
                        });

                        xhr.addEventListener('error', function () {
                            hideFloatingIndicator();
                            document.getElementById('user-upload-status').textContent = 'Error: Upload to Spaces failed. Please try again.';
                            document.getElementById('user-upload-progress-bar').classList.remove('bg-indigo-600');
                            document.getElementById('user-upload-progress-bar').classList.add('bg-red-600');
                            document.getElementById('user-upload-form-buttons').style.display = 'flex';
                        });

                        xhr.open('PUT', presign.upload_url);
                        xhr.setRequestHeader('Content-Type', file.type || 'application/octet-stream');
                        if (presign.headers) {
                            Object.keys(presign.headers).forEach((key) => {
                                const lower = String(key).toLowerCase();
                                if (lower === 'host' || lower === 'content-length') return;
                                if (lower === 'content-type') return;
                                try { xhr.setRequestHeader(key, presign.headers[key]); } catch (e) {}
                            });
                        }
                        xhr.send(file);
                    })
                    .catch((err) => {
                        document.getElementById('user-upload-status').textContent = 'Error: ' + (err?.message || 'Upload failed.');
                        document.getElementById('user-upload-progress-bar').classList.remove('bg-indigo-600');
                        document.getElementById('user-upload-progress-bar').classList.add('bg-red-600');
                        document.getElementById('user-upload-form-buttons').style.display = 'flex';
                    });
                }
            });
        });
    </script>
</div>
@endsection


