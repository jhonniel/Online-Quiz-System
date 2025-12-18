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
                <a href="{{ route('user.files.index') }}" class="text-indigo-600 hover:text-indigo-800">Home</a>
                @foreach($breadcrumbs as $breadcrumb)
                    <span class="text-gray-400">/</span>
                    <a href="{{ route('user.files.index', ['folder_id' => $breadcrumb->id]) }}" class="text-indigo-600 hover:text-indigo-800">{{ $breadcrumb->name }}</a>
                @endforeach
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            @if($files->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4">
                    @foreach($files as $item)
                        <div class="group relative bg-gray-50 rounded-lg border border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all duration-200 p-3">
                            @if($item->isFolder())
                                <a href="{{ route('user.files.index', ['folder_id' => $item->id]) }}" class="block text-center">
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

                                        if ($isImage && $item->thumbnail_path) {
                                            try {
                                                $thumbnailUrl = Storage::disk('digitalocean')->url($item->thumbnail_path);
                                            } catch (\Exception $e) {
                                                try {
                                                    $thumbnailUrl = Storage::disk('digitalocean')->url($item->path);
                                                } catch (\Exception $e2) {}
                                            }
                                        } elseif ($isImage) {
                                            try {
                                                $thumbnailUrl = Storage::disk('digitalocean')->url($item->path);
                                            } catch (\Exception $e) {}
                                        }
                                    @endphp

                                    <div class="flex justify-center mb-2 h-20 overflow-hidden rounded bg-gray-100">
                                        @if($thumbnailUrl)
                                            <img src="{{ $thumbnailUrl }}" alt="{{ $item->name }}"
                                                 class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                                 onclick="openUserPreviewModal('{{ $item->id }}', '{{ addslashes($item->name) }}', '{{ $item->mime_type }}', '{{ Storage::disk('digitalocean')->url($item->path) }}')">
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
                                                <button onclick="openUserPreviewModal('{{ $item->id }}', '{{ addslashes($item->name) }}', '{{ $item->mime_type }}', '{{ Storage::disk('digitalocean')->url($item->path) }}')"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Preview</button>
                                                <a href="{{ route('user.files.download', $item) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Download</a>
                                            @elseif($item->isFolder())
                                                <a href="{{ route('user.files.index', ['folder_id' => $item->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Open</a>
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
                <form action="{{ route('user.files.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? null }}">
                    <div class="mb-4">
                        <label for="user-file" class="block text-sm font-medium text-gray-700 mb-2">Select File (Max: 5GB)</label>
                        <input type="file" name="file" id="user-file" required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">Maximum file size: 5GB</p>
                    </div>
                    <div class="mb-4">
                        <label for="user-description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" id="user-description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('user-upload-file-modal').classList.add('hidden')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                        <button type="submit"
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
                <form action="{{ route('user.files.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? null }}">
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

    <script>
        const USER_FILES_DOWNLOAD_URL = @json(route('user.files.download', ['file' => '__FILE__']));

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
                video.className = 'max-w-full max-h-[70vh] mx-auto rounded-lg';
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
    </script>
</div>
@endsection


