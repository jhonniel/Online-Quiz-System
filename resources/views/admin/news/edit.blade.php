@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit News</h1>
                <p class="text-sm text-gray-600 mt-1">Update news article details</p>
            </div>
            <a href="{{ route('admin.news.index') }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.news.update', $news) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        @method('PUT')

        <!-- Title -->
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" id="title" value="{{ old('title', $news->title) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Content -->
        <div>
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content <span class="text-red-500">*</span></label>
            <textarea name="content" id="content" rows="10" required
                      class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('content', $news->content) }}</textarea>
            @error('content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">You can use HTML tags for formatting</p>
        </div>

        <!-- Category -->
        <div>
            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="category" id="category" onchange="handleCategoryChange()" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select category</option>
                @foreach($allCategories as $cat)
                    <option value="{{ $cat }}" {{ old('category', $news->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
                <option value="__new__">+ Add New Category</option>
            </select>
            <div id="new-category-input" class="mt-2 hidden">
                <input type="text" name="new_category" id="new_category" placeholder="Enter new category name"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <p class="mt-1 text-xs text-gray-500">Enter a new category name</p>
            </div>
            @error('category')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Optional: Categorize this news article</p>
        </div>

        <!-- Current Image -->
        @if($news->image_url || $news->image_path)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                <div class="mt-2">
                    @php
                        $currentImageUrl = $news->image_url;
                        if ($news->image_path && !$currentImageUrl) {
                            // Try digitalocean first, then public
                            if (\Illuminate\Support\Facades\Storage::disk('digitalocean')->exists($news->image_path)) {
                                $currentImageUrl = \Illuminate\Support\Facades\Storage::disk('digitalocean')->url($news->image_path);
                            } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($news->image_path)) {
                                $currentImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($news->image_path);
                            }
                        }
                    @endphp
                    <img src="{{ $currentImageUrl }}" alt="Current image" class="h-48 w-auto rounded-lg border border-gray-300">
                </div>
                <p class="mt-1 text-xs text-gray-500">Upload a new image below to replace this one</p>
            </div>
        @endif

        <!-- Image Upload -->
        <div>
            <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Upload New Image</label>
            <input type="file" name="image" id="image" accept="image/*"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                   onchange="previewImage(this)">
            @error('image')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Upload an image file (JPEG, PNG, JPG, GIF, WEBP - Max 5MB)</p>
            <div id="image-preview" class="mt-4 hidden">
                <img id="preview-img" src="" alt="Preview" class="max-w-xs h-48 object-cover rounded-lg border border-gray-300">
            </div>
        </div>

        <!-- Image URL (Alternative) -->
        <div>
            <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">Or Image URL</label>
            <input type="url" name="image_url" id="image_url" value="{{ old('image_url', $news->image_url) }}"
                   placeholder="https://example.com/image.jpg"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('image_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Alternative: Provide an image URL instead of uploading</p>
        </div>

        <!-- Author -->
        <div>
            <label for="author" class="block text-sm font-medium text-gray-700 mb-2">Author</label>
            <input type="text" name="author" id="author" value="{{ old('author', $news->author) }}"
                   placeholder="Leave empty to use your name"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('author')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Published At -->
        <div>
            <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">Published Date</label>
            <input type="datetime-local" name="published_at" id="published_at" 
                   value="{{ old('published_at', $news->published_at ? $news->published_at->format('Y-m-d\TH:i') : '') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('published_at')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Leave empty to publish immediately when checked</p>
        </div>

        <!-- Is Published -->
        <div class="flex items-center">
            <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $news->is_published) ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="is_published" class="ml-2 block text-sm text-gray-700">
                Publish immediately
            </label>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.news.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update News
            </button>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
    }
}

function handleCategoryChange() {
    const categorySelect = document.getElementById('category');
    const newCategoryInput = document.getElementById('new-category-input');
    const newCategoryField = document.getElementById('new_category');
    
    if (categorySelect.value === '__new__') {
        newCategoryInput.classList.remove('hidden');
        newCategoryField.focus();
        categorySelect.name = ''; // Disable the select
    } else {
        newCategoryInput.classList.add('hidden');
        newCategoryField.value = '';
        categorySelect.name = 'category'; // Re-enable the select
    }
}

// Handle form submission to use new_category if provided
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const categorySelect = document.getElementById('category');
            const newCategoryField = document.getElementById('new_category');
            
            if (categorySelect.value === '__new__' && newCategoryField.value.trim()) {
                // Create a hidden input with the new category value
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'category';
                hiddenInput.value = newCategoryField.value.trim();
                form.appendChild(hiddenInput);
            }
        });
    }
});
</script>
@endsection
