@extends('layouts.admin')

@section('page-title', 'Edit Stack')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Edit Stack</h2>
                <p class="mt-1 text-sm text-gray-600">Update the technology stack information.</p>
            </div>

            <form action="{{ route('admin.stacks.update', $stack) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Stack Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Stack Name *</label>
                    <div class="mt-1">
                        <input type="text" name="name" id="name" required
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               placeholder="e.g., Laravel, React, Vue.js"
                               value="{{ old('name', $stack->name) }}">
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Icon -->
                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700">Icon</label>
                    <div class="mt-1">
                        <input type="text" name="icon" id="icon"
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               placeholder="Icon class (e.g., fab fa-laravel) or image URL"
                               value="{{ old('icon', $stack->icon) }}">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Enter an icon class (Font Awesome, etc.) or an image URL</p>
                    @error('icon')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Upload -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Stack Image</label>

                    @if($stack->image)
                        <div class="mt-2 mb-3">
                            <p class="text-sm text-gray-600 mb-2">Current Image:</p>
                            <img src="{{ $stack->image_url }}" alt="{{ $stack->name }}" class="h-20 w-20 object-contain border border-gray-300 rounded-md p-2 bg-gray-50">
                            <div class="mt-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Remove current image</span>
                                </label>
                            </div>
                        </div>
                    @endif

                    <div class="mt-1">
                        <input type="file" name="image" id="image" accept="image/*"
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Upload a new image for the stack (JPEG, PNG, JPG, GIF, SVG, WEBP - Max 5MB). Leave empty to keep current image.</p>
                    @error('image')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Color -->
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700">Color</label>
                    <div class="mt-1">
                        <input type="text" name="color" id="color"
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               placeholder="e.g., #FF5733, blue, primary"
                               value="{{ old('color', $stack->color) }}">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Optional color for the stack badge</p>
                    @error('color')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Order -->
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700">Display Order</label>
                    <div class="mt-1">
                        <input type="number" name="order" id="order" min="0"
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               placeholder="0"
                               value="{{ old('order', $stack->order) }}">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Lower numbers appear first. Default is 0.</p>
                    @error('order')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $stack->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Active
                    </label>
                </div>
                <p class="text-sm text-gray-500">Only active stacks will be displayed on the landing page.</p>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.stacks.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Update Stack
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
