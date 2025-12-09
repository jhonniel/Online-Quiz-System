@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <a href="{{ route('admin.hiring-positions.index') }}" class="mr-4 text-white hover:text-indigo-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Edit Hiring Position</h1>
                <p class="text-indigo-100">Update job position details</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        <form action="{{ route('admin.hiring-positions.update', $hiringPosition) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Position Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required
                           value="{{ old('title', $hiringPosition->title) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="md:col-span-2">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                        URL Slug (leave empty to auto-generate)
                    </label>
                    <input type="text" name="slug" id="slug"
                           value="{{ old('slug', $hiringPosition->slug) }}"
                           placeholder="e.g., software-developer, data-analyst"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Used in the application URL. Auto-generated from title if left empty.</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department -->
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <input type="text" name="department" id="department"
                           value="{{ old('department', $hiringPosition->department) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <input type="text" name="location" id="location"
                           value="{{ old('location', $hiringPosition->location) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Employment Type -->
                <div>
                    <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-2">Employment Type</label>
                    <select name="employment_type" id="employment_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select type</option>
                        <option value="Full-time" {{ old('employment_type', $hiringPosition->employment_type) == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                        <option value="Part-time" {{ old('employment_type', $hiringPosition->employment_type) == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                        <option value="Contract" {{ old('employment_type', $hiringPosition->employment_type) == 'Contract' ? 'selected' : '' }}>Contract</option>
                        <option value="Internship" {{ old('employment_type', $hiringPosition->employment_type) == 'Internship' ? 'selected' : '' }}>Internship</option>
                        <option value="Remote" {{ old('employment_type', $hiringPosition->employment_type) == 'Remote' ? 'selected' : '' }}>Remote</option>
                    </select>
                </div>

                <!-- Application Deadline -->
                <div>
                    <label for="application_deadline" class="block text-sm font-medium text-gray-700 mb-2">Application Deadline</label>
                    <input type="date" name="application_deadline" id="application_deadline"
                           value="{{ old('application_deadline', $hiringPosition->application_deadline?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Salary Range -->
                <div>
                    <label for="salary_min" class="block text-sm font-medium text-gray-700 mb-2">Salary Min</label>
                    <input type="number" name="salary_min" id="salary_min" step="0.01" min="0"
                           value="{{ old('salary_min', $hiringPosition->salary_min) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="salary_max" class="block text-sm font-medium text-gray-700 mb-2">Salary Max</label>
                    <input type="number" name="salary_max" id="salary_max" step="0.01" min="0"
                           value="{{ old('salary_max', $hiringPosition->salary_max) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $hiringPosition->description) }}</textarea>
                </div>

                <!-- Thumbnail -->
                <div class="md:col-span-2 space-y-2">
                    <label for="thumbnail" class="block text-sm font-medium text-gray-700">Thumbnail (Optional)</label>
                    @if($hiringPosition->thumbnail_url)
                        <div class="mb-2">
                            <img src="{{ $hiringPosition->thumbnail_url }}" alt="Current Thumbnail" class="h-24 w-auto rounded border border-gray-200 object-cover">
                        </div>
                    @endif
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">Upload an image for the position tile. Max 5MB.</p>
                    @error('thumbnail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Requirements -->
                <div class="md:col-span-2">
                    <label for="requirements" class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                    <textarea name="requirements" id="requirements" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="List the requirements for this position...">{{ old('requirements', $hiringPosition->requirements) }}</textarea>
                </div>

                <!-- Responsibilities -->
                <div class="md:col-span-2">
                    <label for="responsibilities" class="block text-sm font-medium text-gray-700 mb-2">Responsibilities</label>
                    <textarea name="responsibilities" id="responsibilities" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="List the responsibilities for this position...">{{ old('responsibilities', $hiringPosition->responsibilities) }}</textarea>
                </div>

                <!-- Is Active -->
                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $hiringPosition->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">Active (accepting applications)</span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.hiring-positions.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Update Position
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-generate slug from title (only if slug is empty or was auto-generated)
const slugInput = document.getElementById('slug');
if (!slugInput.value) {
    document.getElementById('title').addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
    });
}

slugInput.addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});
</script>
@endsection

