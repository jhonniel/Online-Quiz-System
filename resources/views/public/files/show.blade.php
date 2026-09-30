<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $item->name }} - Shared Files</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Public shared link</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $item->name }}</h1>
            @if($item->description)
                <p class="mt-2 text-sm text-gray-600">{{ $item->description }}</p>
            @endif
        </div>

        @if($isFilePreview ?? false)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $item->display_type_label }}</p>
                        <p class="text-xs text-gray-500">{{ $item->formatted_size }}</p>
                    </div>
                    <a href="{{ route('public.files.download', $item->uuid) }}"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Download
                    </a>
                </div>
                <div id="public-file-preview-content" class="p-4 min-h-[320px] bg-gray-50"></div>
            </div>
        @else
            @if($breadcrumbs->isNotEmpty())
                <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
                    <a href="{{ route('public.files.show', $root->uuid) }}" class="text-indigo-600 hover:text-indigo-800">Home</a>
                    @foreach($breadcrumbs as $breadcrumb)
                        <span class="text-gray-400">/</span>
                        <a href="{{ route('public.files.show', $breadcrumb->uuid) }}" class="text-indigo-600 hover:text-indigo-800">{{ $breadcrumb->name }}</a>
                    @endforeach
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-700">{{ $item->name }}</span>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                @if($files->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4">
                        @foreach($files as $fileItem)
                            @php
                                $publicUrl = route('public.files.show', $fileItem->uuid);
                                $viewUrl = route('public.files.view', $fileItem->uuid);
                                $isImage = $fileItem->mime_type && str_starts_with($fileItem->mime_type, 'image/');
                            @endphp
                            <div class="bg-gray-50 rounded-lg border border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all p-3 text-center">
                                @if($fileItem->isFolder())
                                    <a href="{{ $publicUrl }}" class="block">
                                        <div class="flex justify-center mb-2">
                                            <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-900 truncate" title="{{ $fileItem->name }}">{{ $fileItem->name }}</h3>
                                        <p class="text-xs font-medium text-indigo-600 mt-1">Folder</p>
                                    </a>
                                @else
                                    <div class="cursor-pointer" onclick="openPublicPreview('{{ addslashes($fileItem->name) }}', '{{ addslashes($fileItem->mime_type ?? '') }}', '{{ $viewUrl }}', '{{ route('public.files.download', $fileItem->uuid) }}')">
                                        <div class="flex justify-center mb-2 h-20 overflow-hidden rounded bg-gray-100">
                                            @if($isImage)
                                                <img src="{{ $viewUrl }}" alt="{{ $fileItem->name }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-indigo-600 text-xs font-semibold">
                                                    {{ $fileItem->display_type_label }}
                                                </div>
                                            @endif
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-900 truncate" title="{{ $fileItem->name }}">{{ $fileItem->name }}</h3>
                                        <p class="text-xs font-medium text-indigo-600 mt-1">{{ $fileItem->display_type_label }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $fileItem->formatted_size }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center text-sm text-gray-500">This folder is empty.</div>
                @endif
            </div>
        @endif
    </div>

    <div id="public-preview-modal" class="hidden fixed inset-0 bg-gray-900/75 z-50 overflow-y-auto">
        <div class="relative top-4 mx-auto mb-4 w-full max-w-5xl rounded-lg border bg-white p-5 shadow-lg">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 id="public-preview-title" class="text-lg font-medium text-gray-900"></h3>
                <div class="flex items-center gap-2">
                    <a id="public-preview-download" href="#" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Download</a>
                    <button type="button" onclick="document.getElementById('public-preview-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
            <div id="public-preview-content-modal" class="min-h-[320px] rounded-lg bg-gray-50 p-4"></div>
        </div>
    </div>

    @include('partials.file-storage-preview-script')

    <script>
        async function openPublicPreview(name, mimeType, viewUrl, downloadUrl) {
            document.getElementById('public-preview-title').textContent = name;
            document.getElementById('public-preview-download').href = downloadUrl;
            document.getElementById('public-preview-modal').classList.remove('hidden');
            await renderFileStoragePreview(document.getElementById('public-preview-content-modal'), {
                name: name,
                mimeType: mimeType,
                url: viewUrl,
            });
        }

        @if($isFilePreview ?? false)
        document.addEventListener('DOMContentLoaded', function () {
            renderFileStoragePreview(document.getElementById('public-file-preview-content'), {
                name: @json($item->name),
                mimeType: @json($item->mime_type ?? ''),
                url: @json(route('public.files.view', $item->uuid)),
            });
        });
        @endif
    </script>
</body>
</html>
