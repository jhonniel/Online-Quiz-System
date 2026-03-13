@extends('layouts.admin')

@section('page-title', 'File Storage')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">File Storage</span>
        </div>
    </li>
@endsection

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">File Storage</h1>
                <p class="mt-2 text-sm text-gray-600">Upload and organize files in folders</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <button onclick="document.getElementById('create-folder-modal').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Folder
                </button>
                <button onclick="document.getElementById('upload-file-modal').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Upload File
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Breadcrumb -->
    @if(count($breadcrumbs) > 0)
        <div class="mb-6 flex items-center space-x-2 text-sm">
            <a href="{{ url('/admin/files') }}" class="text-indigo-600 hover:text-indigo-800">Home</a>
            @foreach($breadcrumbs as $breadcrumb)
                <span class="text-gray-400">/</span>
                <a href="{{ url('/admin/files?' . http_build_query(['folder_id' => $breadcrumb->id])) }}" class="text-indigo-600 hover:text-indigo-800">{{ $breadcrumb->name }}</a>
            @endforeach
        </div>
    @endif

    <!-- Files and Folders Grid -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        @if($files->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 p-6">
                @foreach($files as $item)
                    <div class="group relative bg-gray-50 rounded-lg border border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all duration-200 p-4">
                        @if($item->isFolder())
                            @php
                                $sharedUsers = $item->sharedWith ?? collect();
                                $sharedCount = $sharedUsers->count();
                            @endphp

                            @if($sharedCount > 0)
                                <div class="absolute top-2 left-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                                     title="Shared with {{ $sharedCount }} user{{ $sharedCount !== 1 ? 's' : '' }}">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Shared
                                </div>
                            @endif

                            <a href="{{ url('/admin/files?' . http_build_query(['folder_id' => $item->id])) }}" class="block text-center">
                                <div class="flex justify-center mb-3">
                                    <svg class="w-16 h-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 truncate" title="{{ $item->name }}">{{ $item->name }}</h3>
                                <p class="text-xs text-gray-500 mt-1">Folder</p>
                                <p class="text-xs text-gray-600 mt-0.5" title="Owner">Owner: {{ $item->uploader->name ?? 'N/A' }}</p>
                            </a>

                            <div class="mt-2 flex justify-center">
                                <button type="button"
                                        onclick="openShareModal({{ $item->id }}, 'folder')"
                                        class="inline-flex items-center px-2 py-1 rounded-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs"
                                        title="View shared with ({{ $sharedCount }})">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Shared: {{ $sharedCount }}
                                </button>
                            </div>
                        @else
                            <div class="text-center">
                                @php
                                    $isImage = $item->mime_type && str_starts_with($item->mime_type, 'image/');
                                    $thumbnailUrl = null;
                                    if ($isImage) {
                                        // Use app route so it works for private Spaces too (redirects to signed URL)
                                        $thumbnailUrl = url('/admin/files/' . $item->id . '/view');
                                    }
                                @endphp

                                <div class="flex justify-center mb-3 h-24 overflow-hidden rounded-lg bg-gray-100">
                                    @if($thumbnailUrl)
                                        <img src="{{ $thumbnailUrl }}" alt="{{ $item->name }}"
                                             class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                             onclick="openPreviewModal('{{ $item->id }}', '{{ addslashes($item->name) }}', '{{ $item->mime_type }}', '{{ url('/admin/files/' . $item->id . '/view') }}')">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            @if(str_starts_with($item->mime_type ?? '', 'application/pdf'))
                                                <svg class="w-12 h-12 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif(str_starts_with($item->mime_type ?? '', 'video/'))
                                                <svg class="w-12 h-12 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                </svg>
                                            @elseif(str_starts_with($item->mime_type ?? '', 'audio/'))
                                                <svg class="w-12 h-12 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 truncate" title="{{ $item->name }}">{{ $item->name }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ $item->formatted_size }}</p>
                                <p class="text-xs text-gray-600 mt-0.5" title="Owner">Owner: {{ $item->uploader->name ?? 'N/A' }}</p>
                            </div>
                        @endif

                        <!-- Actions Menu (Admin: Share, Rename, Delete for all items) -->
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="p-1 rounded-md hover:bg-gray-200 text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                    <div class="py-1">
                                        @if($item->isFile())
                                            <button onclick="openPreviewModal('{{ $item->id }}', '{{ addslashes($item->name) }}', '{{ $item->mime_type }}', '{{ url('/admin/files/' . $item->id . '/view') }}')"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Preview</button>
                                            <a href="{{ url('/admin/files/' . $item->id . '/download') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Download</a>
                                        @endif
                                        <button onclick="openShareModal({{ $item->id }}, '{{ $item->type }}')"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Share / View shared with</button>
                                        <button onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ addslashes($item->description ?? '') }}', '{{ $item->type }}')"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Rename</button>
                                        <form action="{{ url('/admin/files/' . $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this {{ $item->type }}?{{ $item->isFolder() ? ' This will delete all contents inside.' : '' }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $files->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No files or folders</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by uploading a file or creating a folder.</p>
            </div>
        @endif
    </div>

    <!-- Upload File Modal -->
    <div id="upload-file-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Upload File</h3>
                    <button id="close-upload-modal" onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Upload Progress (Hidden by default) -->
                <div id="upload-progress-container" class="hidden mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Uploading...</span>
                        <span id="upload-percentage" class="text-sm font-medium text-indigo-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="upload-progress-bar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                    <p id="upload-status" class="text-xs text-gray-500 mt-2">Preparing upload...</p>
                </div>

                <form id="upload-file-form" action="{{ url('/admin/files') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? null }}">
                    <div class="mb-4">
                        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Select File (Max: 5GB)</label>
                        <input type="file" name="file" id="file" required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">Maximum file size: 5GB</p>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" id="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div id="upload-form-buttons" class="flex justify-end space-x-3">
                        <button type="button" onclick="closeUploadModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" id="upload-submit-btn"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div id="create-folder-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Create Folder</h3>
                    <button onclick="document.getElementById('create-folder-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form action="{{ url('/admin/files/create-folder') }}" method="POST">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? null }}">
                    <div class="mb-4">
                        <label for="folder_name" class="block text-sm font-medium text-gray-700 mb-2">Folder Name</label>
                        <input type="text" name="name" id="folder_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label for="folder_description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" id="folder_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('create-folder-modal').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Rename</h3>
                    <button onclick="document.getElementById('edit-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="edit-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" id="edit_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" id="edit_description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="share-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Share <span id="share-item-type"></span></h3>
                    <button onclick="document.getElementById('share-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form id="share-form" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="share_user_id" class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <select name="user_id" id="share_user_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Choose a user...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4 space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="can_view" value="1" checked
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Can View</span>
                        </label>
                        <label class="flex items-center" id="can-upload-container">
                            <input type="checkbox" name="can_upload" value="1"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Can Upload (Folders only)</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('share-modal').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Share
                        </button>
                    </div>
                </form>

                <!-- Shared users list -->
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-gray-900">Shared with</h4>
                        <button type="button" id="refresh-shared-users-btn"
                                class="text-xs text-indigo-600 hover:text-indigo-800 underline"
                                onclick="refreshSharedUsers()">
                            Refresh
                        </button>
                    </div>
                    <div id="shared-users-loading" class="text-sm text-gray-500">Loading...</div>
                    <div id="shared-users-empty" class="hidden text-sm text-gray-500">Not shared with anyone yet.</div>
                    <div id="shared-users-error" class="hidden text-sm text-red-600"></div>
                    <ul id="shared-users-list" class="hidden divide-y divide-gray-200 max-h-56 overflow-y-auto"></ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Use named-route templates (prevents bad URLs / 404s)
        const ADMIN_FILES_UPDATE_URL = @json(url('/admin/files/__FILE__'));
        const ADMIN_FILES_SHARE_URL = @json(url('/admin/files/__FILE__/share'));
        const ADMIN_FILES_UNSHARE_URL = @json(url('/admin/files/__FILE__/unshare'));
        const ADMIN_FILES_SHARED_USERS_URL = @json(url('/admin/files/__FILE__/shared-users'));
        const ADMIN_FILES_DOWNLOAD_URL = @json(url('/admin/files/__FILE__/download'));
        const ADMIN_FILES_PRESIGN_URL = @json(url('/admin/files/presign'));
        const ADMIN_FILES_CONFIRM_URL = @json(url('/admin/files/confirm'));
        const ADMIN_FILES_MULTIPART_INITIATE_URL = @json(url('/admin/files/multipart/initiate'));
        const ADMIN_FILES_MULTIPART_PRESIGN_CHUNK_URL = @json(url('/admin/files/multipart/presign-chunk'));
        const ADMIN_FILES_MULTIPART_COMPLETE_URL = @json(url('/admin/files/multipart/complete'));
        const ADMIN_FILES_MULTIPART_ABORT_URL = @json(url('/admin/files/multipart/abort'));

        let currentShareItemId = null;

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function setSharedUsersState(state) {
            const loading = document.getElementById('shared-users-loading');
            const empty = document.getElementById('shared-users-empty');
            const error = document.getElementById('shared-users-error');
            const list = document.getElementById('shared-users-list');

            loading.classList.add('hidden');
            empty.classList.add('hidden');
            error.classList.add('hidden');
            list.classList.add('hidden');

            if (state === 'loading') loading.classList.remove('hidden');
            if (state === 'empty') empty.classList.remove('hidden');
            if (state === 'error') error.classList.remove('hidden');
            if (state === 'list') list.classList.remove('hidden');
        }

        function renderSharedUsers(users) {
            const list = document.getElementById('shared-users-list');
            list.innerHTML = '';

            users.forEach(u => {
                const canView = !!(u.pivot && u.pivot.can_view);
                const canUpload = !!(u.pivot && u.pivot.can_upload);

                const li = document.createElement('li');
                li.className = 'py-2 flex items-center justify-between';

                const left = document.createElement('div');
                left.className = 'min-w-0';
                left.innerHTML = `
                    <div class="text-sm font-medium text-gray-900 truncate">${escapeHtml(u.name)}</div>
                    <div class="text-xs text-gray-500 truncate">${escapeHtml(u.email)}</div>
                    <div class="mt-1 flex flex-wrap gap-1">
                        ${canView ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-green-100 text-green-800">View</span>' : ''}
                        ${canUpload ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-indigo-100 text-indigo-800">Upload</span>' : ''}
                    </div>
                `;

                const right = document.createElement('div');
                right.className = 'flex-shrink-0 pl-2';

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'text-xs text-red-600 hover:text-red-800 underline';
                btn.textContent = 'Remove';
                btn.onclick = () => unshareUser(currentShareItemId, u.id);

                right.appendChild(btn);
                li.appendChild(left);
                li.appendChild(right);
                list.appendChild(li);
            });
        }

        function loadSharedUsers(fileId) {
            currentShareItemId = fileId;
            setSharedUsersState('loading');
            document.getElementById('shared-users-error').textContent = '';

            const url = ADMIN_FILES_SHARED_USERS_URL.replace('__FILE__', fileId);
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(async (res) => {
                    const data = await res.json().catch(() => null);
                    if (!res.ok) throw new Error((data && data.message) ? data.message : 'Failed to load shared users.');
                    return data;
                })
                .then((users) => {
                    if (!Array.isArray(users) || users.length === 0) {
                        setSharedUsersState('empty');
                        return;
                    }
                    renderSharedUsers(users);
                    setSharedUsersState('list');
                })
                .catch((err) => {
                    document.getElementById('shared-users-error').textContent = err?.message || 'Failed to load shared users.';
                    setSharedUsersState('error');
                });
        }

        function refreshSharedUsers() {
            if (!currentShareItemId) return;
            loadSharedUsers(currentShareItemId);
        }

        function unshareUser(fileId, userId) {
            if (!fileId || !userId) return;
            if (!confirm('Remove this user\'s access?')) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const url = ADMIN_FILES_UNSHARE_URL.replace('__FILE__', fileId);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ user_id: userId }),
            })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || 'Failed to remove sharing permission.');
                return data;
            })
            .then(() => {
                loadSharedUsers(fileId);
            })
            .catch((err) => {
                alert(err?.message || 'Failed to remove sharing permission.');
            });
        }

        function openEditModal(id, name, description, type) {
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description || '';
            document.getElementById('edit-form').action = ADMIN_FILES_UPDATE_URL.replace('__FILE__', id);
            document.getElementById('edit-modal').classList.remove('hidden');
        }

        function openShareModal(id, type) {
            document.getElementById('share-item-type').textContent = type === 'folder' ? 'Folder' : 'File';
            document.getElementById('share-form').action = ADMIN_FILES_SHARE_URL.replace('__FILE__', id);

            // Show/hide can_upload checkbox based on type
            const canUploadContainer = document.getElementById('can-upload-container');
            if (type === 'folder') {
                canUploadContainer.style.display = 'flex';
            } else {
                canUploadContainer.style.display = 'none';
                document.querySelector('input[name="can_upload"]').checked = false;
            }

            // Reset form
            document.getElementById('share_user_id').value = '';
            document.querySelector('input[name="can_view"]').checked = true;

            document.getElementById('share-modal').classList.remove('hidden');

            // Load shared users list (owner-only endpoint; shows who already has access)
            loadSharedUsers(id);
        }

        function openPreviewModal(id, name, mimeType, url) {
            document.getElementById('preview-file-name').textContent = name;
            document.getElementById('preview-file-url').href = url;
            document.getElementById('preview-download-url').href = ADMIN_FILES_DOWNLOAD_URL.replace('__FILE__', id);

            const previewContent = document.getElementById('preview-content');
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
                video.className = 'max-w-full max-h-[70vh] mx-auto rounded-lg';
                previewContent.appendChild(video);
            } else if (mimeType && mimeType.startsWith('audio/')) {
                const audio = document.createElement('audio');
                audio.src = url;
                audio.controls = true;
                audio.className = 'w-full mx-auto';
                previewContent.appendChild(audio);
            } else {
                previewContent.innerHTML = '<div class="text-center py-12"><p class="text-gray-500">Preview not available for this file type.</p><p class="text-sm text-gray-400 mt-2">Click download to view the file.</p></div>';
            }

            document.getElementById('preview-modal').classList.remove('hidden');
        }

        function closeUploadModal() {
            document.getElementById('upload-file-modal').classList.add('hidden');
            resetUploadForm();
        }

        function resetUploadForm() {
            document.getElementById('upload-file-form').reset();
            document.getElementById('upload-progress-container').classList.add('hidden');
            document.getElementById('upload-form-buttons').style.display = 'flex';
            document.getElementById('upload-progress-bar').style.width = '0%';
            document.getElementById('upload-percentage').textContent = '0%';
            document.getElementById('upload-status').textContent = 'Preparing upload...';
        }

        // Handle file upload with chunked multipart upload
        document.getElementById('upload-file-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const fileInput = document.getElementById('file');
            const file = fileInput.files[0];

            if (!file) {
                alert('Please select a file to upload.');
                return;
            }

            // Check file size (5GB = 5368709120 bytes)
            const maxSize = 5368709120; // 5GB in bytes
            if (file.size > maxSize) {
                alert('File size exceeds 5GB limit. Please select a smaller file.');
                return;
            }

            // Show progress container
            document.getElementById('upload-progress-container').classList.remove('hidden');
            document.getElementById('upload-form-buttons').style.display = 'none';
            document.getElementById('upload-status').textContent = 'Preparing chunked upload for ' + file.name + '...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const folderId = formData.get('folder_id') || null;
            const description = formData.get('description') || '';

            // Use chunked upload for files larger than 10MB, otherwise use single PUT
            const CHUNK_SIZE = 10 * 1024 * 1024; // 10MB
            const useChunked = file.size > CHUNK_SIZE;

            if (useChunked) {
                // Chunked multipart upload
                let uploadId = null;
                let path = null;
                let chunkSize = CHUNK_SIZE;
                const totalChunks = Math.ceil(file.size / chunkSize);
                const uploadedParts = [];

                // Step 1: Initiate multipart upload
                fetch(ADMIN_FILES_MULTIPART_INITIATE_URL, {
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
                        throw new Error(data.message || 'Failed to initiate multipart upload.');
                    }
                    return data;
                })
                .then((initData) => {
                    uploadId = initData.upload_id;
                    path = initData.path;
                    chunkSize = initData.chunk_size || CHUNK_SIZE;

                    // Step 2: Upload chunks sequentially
                    let currentChunk = 0;
                    const uploadChunk = (chunkIndex) => {
                        if (chunkIndex >= totalChunks) {
                            // All chunks uploaded, complete multipart upload
                            document.getElementById('upload-status').textContent = 'Completing upload...';

                            fetch(ADMIN_FILES_MULTIPART_COMPLETE_URL, {
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
                                if (!res.ok) {
                                    throw new Error(data.message || 'Failed to complete upload.');
                                }
                                return data;
                            })
                            .then(() => {
                                document.getElementById('upload-status').textContent = 'Saved! Reloading...';
                                document.getElementById('upload-progress-bar').style.width = '100%';
                                document.getElementById('upload-percentage').textContent = '100%';
                                setTimeout(() => window.location.reload(), 800);
                            })
                            .catch((err) => {
                                document.getElementById('upload-status').textContent = 'Error: ' + (err?.message || 'Failed to complete upload.');
                                document.getElementById('upload-progress-bar').classList.remove('bg-indigo-600');
                                document.getElementById('upload-progress-bar').classList.add('bg-red-600');
                                document.getElementById('upload-form-buttons').style.display = 'flex';
                            });
                            return;
                        }

                        const start = chunkIndex * chunkSize;
                        const end = Math.min(start + chunkSize, file.size);
                        const chunk = file.slice(start, end);
                        const partNumber = chunkIndex + 1;

                        // Get presigned URL for this chunk
                        fetch(ADMIN_FILES_MULTIPART_PRESIGN_CHUNK_URL, {
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
                            if (!res.ok) {
                                throw new Error(data.message || 'Failed to get presigned URL for chunk.');
                            }
                            return data;
                        })
                        .then((presign) => {
                            // Upload chunk
                            const chunkXhr = new XMLHttpRequest();

                            chunkXhr.addEventListener('load', function() {
                                if (chunkXhr.status >= 200 && chunkXhr.status < 300) {
                                    const etag = chunkXhr.getResponseHeader('ETag') || chunkXhr.getResponseHeader('etag');
                                    if (!etag) {
                                        throw new Error('Missing ETag in chunk response.');
                                    }

                                    uploadedParts.push({
                                        part_number: partNumber,
                                        etag: etag.replace(/"/g, ''), // Remove quotes from ETag
                                    });

                                    // Update progress
                                    const overallProgress = ((chunkIndex + 1) / totalChunks) * 100;
                                    document.getElementById('upload-progress-bar').style.width = overallProgress + '%';
                                    document.getElementById('upload-percentage').textContent = Math.round(overallProgress) + '%';

                                    const uploadedMB = ((chunkIndex + 1) * chunkSize / 1048576).toFixed(2);
                                    const totalMB = (file.size / 1048576).toFixed(2);
                                    document.getElementById('upload-status').textContent =
                                        `Uploading chunk ${partNumber}/${totalChunks}: ${uploadedMB} MB / ${totalMB} MB`;

                                    // Upload next chunk
                                    uploadChunk(chunkIndex + 1);
                                } else {
                                    throw new Error('Chunk upload failed (HTTP ' + chunkXhr.status + ').');
                                }
                            });

                            chunkXhr.addEventListener('error', function() {
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
                            document.getElementById('upload-status').textContent = 'Error: ' + (err?.message || 'Chunk upload failed.');
                            document.getElementById('upload-progress-bar').classList.remove('bg-indigo-600');
                            document.getElementById('upload-progress-bar').classList.add('bg-red-600');
                            document.getElementById('upload-form-buttons').style.display = 'flex';
                        });
                    };

                    // Start uploading chunks
                    uploadChunk(0);
                })
                .catch((err) => {
                    document.getElementById('upload-status').textContent = 'Error: ' + (err?.message || 'Failed to initiate upload.');
                    document.getElementById('upload-progress-bar').classList.remove('bg-indigo-600');
                    document.getElementById('upload-progress-bar').classList.add('bg-red-600');
                    document.getElementById('upload-form-buttons').style.display = 'flex';
                });
            } else {
                // Single PUT upload for smaller files (original method)
                fetch(ADMIN_FILES_PRESIGN_URL, {
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
                        throw new Error(data.message || 'Failed to prepare upload.');
                    }
                    return data;
                })
                .then((presign) => {
                    const uploadXhr = new XMLHttpRequest();

                    uploadXhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            const roundedPercent = Math.round(percentComplete);

                            document.getElementById('upload-progress-bar').style.width = percentComplete + '%';
                            document.getElementById('upload-percentage').textContent = roundedPercent + '%';

                            const loadedMB = (e.loaded / 1048576).toFixed(2);
                            const totalMB = (e.total / 1048576).toFixed(2);
                            document.getElementById('upload-status').textContent =
                                `Uploading to Spaces: ${loadedMB} MB / ${totalMB} MB (${roundedPercent}%)`;
                        }
                    });

                    uploadXhr.addEventListener('load', function() {
                        if (uploadXhr.status >= 200 && uploadXhr.status < 300) {
                            document.getElementById('upload-status').textContent = 'Upload complete! Saving record...';

                            fetch(ADMIN_FILES_CONFIRM_URL, {
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
                                if (!res.ok) {
                                    throw new Error(data.message || 'Failed to save uploaded file.');
                                }
                                return data;
                            })
                            .then(() => {
                                document.getElementById('upload-status').textContent = 'Saved! Reloading...';
                                document.getElementById('upload-progress-bar').style.width = '100%';
                                document.getElementById('upload-percentage').textContent = '100%';
                                setTimeout(() => window.location.reload(), 800);
                            })
                            .catch((err) => {
                                document.getElementById('upload-status').textContent = 'Error: ' + (err?.message || 'Failed to save file record.');
                                document.getElementById('upload-progress-bar').classList.remove('bg-indigo-600');
                                document.getElementById('upload-progress-bar').classList.add('bg-red-600');
                                document.getElementById('upload-form-buttons').style.display = 'flex';
                            });
                        } else {
                            document.getElementById('upload-status').textContent = 'Error: Upload to Spaces failed (HTTP ' + uploadXhr.status + ').';
                            document.getElementById('upload-progress-bar').classList.remove('bg-indigo-600');
                            document.getElementById('upload-progress-bar').classList.add('bg-red-600');
                            document.getElementById('upload-form-buttons').style.display = 'flex';
                        }
                    });

                    uploadXhr.addEventListener('error', function() {
                        try { console.error('Spaces upload XHR error', uploadXhr); } catch (e) {}
                        document.getElementById('upload-status').textContent = 'Error: Upload to Spaces failed (often CORS). Configure Space CORS to allow your origin + PUT + Content-Type.';
                        document.getElementById('upload-progress-bar').classList.remove('bg-indigo-600');
                        document.getElementById('upload-progress-bar').classList.add('bg-red-600');
                        document.getElementById('upload-form-buttons').style.display = 'flex';
                    });

                    uploadXhr.addEventListener('abort', function() {
                        document.getElementById('upload-status').textContent = 'Upload cancelled.';
                        resetUploadForm();
                    });

                    uploadXhr.open('PUT', presign.upload_url);
                    uploadXhr.setRequestHeader('Content-Type', file.type || 'application/octet-stream');
                    if (presign.headers) {
                        Object.keys(presign.headers).forEach((key) => {
                            const lower = String(key).toLowerCase();
                            if (lower === 'host' || lower === 'content-length') return;
                            if (lower === 'content-type') return;
                            try {
                                uploadXhr.setRequestHeader(key, presign.headers[key]);
                            } catch (e) {
                                // ignore headers the browser disallows
                            }
                        });
                    }

                    uploadXhr.send(file);
                })
                .catch((err) => {
                    document.getElementById('upload-status').textContent = 'Error: ' + (err?.message || 'Upload failed.');
                    document.getElementById('upload-progress-bar').classList.remove('bg-indigo-600');
                    document.getElementById('upload-progress-bar').classList.add('bg-red-600');
                    document.getElementById('upload-form-buttons').style.display = 'flex';
                });
            }
        });
    </script>

    <!-- Preview Modal -->
    <div id="preview-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50">
        <div class="relative top-4 mx-auto p-5 border w-full max-w-5xl shadow-lg rounded-md bg-white mb-4">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 id="preview-file-name" class="text-lg font-medium text-gray-900"></h3>
                    <div class="flex items-center space-x-2">
                        <a id="preview-download-url" href="#" download
                           class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download
                        </a>
                        <button onclick="document.getElementById('preview-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="preview-content" class="bg-gray-50 rounded-lg p-4 min-h-[400px] flex items-center justify-center">
                    <!-- Preview content will be inserted here -->
                </div>
            </div>
        </div>
    </div>
@endsection


