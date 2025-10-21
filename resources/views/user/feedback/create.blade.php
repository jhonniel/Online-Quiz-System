@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col space-y-3">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center">
                <a href="{{ route('user.feedback.index') }}" class="mr-3 p-2 rounded-lg hover:bg-white hover:bg-opacity-20 transition-colors duration-200">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Submit Feedback</h1>
                    <p class="text-indigo-100 text-sm">Help us improve the system by sharing your thoughts</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden flex-1 flex flex-col">
        <div class="px-4 py-5 sm:p-6 h-full overflow-y-auto">
            <div class="mb-6">
                <h2 class="text-lg font-medium text-gray-900">Feedback Submission Form</h2>
                <p class="mt-1 text-sm text-gray-500">Please provide detailed information to help us understand and address your feedback effectively.</p>
            </div>
        <form id="feedback-form" action="{{ route('user.feedback.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Feedback Type -->
            <x-formal-radio-group
                label="Feedback Type"
                name="type"
                :required="true"
                :error="$errors->first('type')"
                help="Select the category that best describes your feedback"
                :options="[
                    'bug_report' => [
                        'icon' => '🐛',
                        'label' => 'Bug Report',
                        'description' => 'Report a problem or error you encountered'
                    ],
                    'feature_request' => [
                        'icon' => '💡',
                        'label' => 'Feature Request',
                        'description' => 'Suggest a new feature or functionality'
                    ],
                    'improvement' => [
                        'icon' => '⚡',
                        'label' => 'Improvement',
                        'description' => 'Suggest improvements to existing features'
                    ],
                    'general' => [
                        'icon' => '💬',
                        'label' => 'General Feedback',
                        'description' => 'General comments or suggestions'
                    ]
                ]"
            />

            <!-- Priority Level -->
            <x-formal-radio-group
                label="Priority Level"
                name="priority"
                :required="true"
                :error="$errors->first('priority')"
                help="How urgent is this feedback?"
                :options="[
                    'low' => [
                        'icon' => '🟢',
                        'label' => 'Low Priority',
                        'description' => 'Nice to have, not urgent'
                    ],
                    'medium' => [
                        'icon' => '🟡',
                        'label' => 'Medium Priority',
                        'description' => 'Important but not critical'
                    ],
                    'high' => [
                        'icon' => '🟠',
                        'label' => 'High Priority',
                        'description' => 'Important and should be addressed soon'
                    ],
                    'critical' => [
                        'icon' => '🔴',
                        'label' => 'Critical Priority',
                        'description' => 'Urgent issue affecting functionality'
                    ]
                ]"
            />

            <!-- Title -->
            <x-formal-input
                label="Title"
                name="title"
                :required="true"
                placeholder="Brief description of your feedback"
                :value="old('title')"
                :error="$errors->first('title')"
                help="Provide a clear, concise title for your feedback"
                icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>'
            />

            <!-- Description -->
            <x-formal-textarea
                label="Description"
                name="description"
                :required="true"
                :rows="6"
                placeholder="Please provide detailed information about your feedback. For bug reports, include steps to reproduce the issue. For feature requests, explain how it would benefit users."
                :value="old('description')"
                :error="$errors->first('description')"
                help="Minimum 10 characters, maximum 2000 characters. Be as detailed as possible to help us understand your feedback."
            />

            <!-- Multiple Image Upload -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Screenshots or Images (Optional - Up to 5)
                    </span>
                </label>

                <!-- Upload Area - Fully Clickable -->
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-400 hover:bg-indigo-50 transition-all duration-200 cursor-pointer"
                     id="image-upload-area"
                     onclick="document.getElementById('images').click()">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium text-indigo-600 hover:text-indigo-500">Click to upload images</span>
                            <span class="text-gray-500"> or drag and drop</span>
                        </div>
                        <p class="text-xs text-gray-500">
                            PNG, JPG, GIF, WEBP up to 5MB each (Maximum 5 images)
                        </p>
                    </div>
                </div>

                <!-- Hidden File Input -->
                <input id="images" name="images[]" type="file" class="sr-only" accept="image/*" multiple onchange="handleMultipleImageUpload(this)">

                <!-- Image Previews -->
                <div id="image-previews" class="hidden mt-3">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3" id="preview-grid">
                        <!-- Image previews will be added here -->
                    </div>
                    <div class="mt-2 text-sm text-gray-600 text-center">
                        <span id="image-count">0</span> image(s) selected for upload
                    </div>
                </div>

                @if($errors->has('images.*'))
                    <p class="mt-1 text-sm text-red-600">{{ $errors->first('images.*') }}</p>
                @endif
                <p class="text-sm text-gray-500">
                    Upload screenshots or images to help us understand the issue better. This is especially helpful for bug reports. You can upload up to 5 images at once.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('user.feedback.index') }}">
                    <x-formal-button variant="outline" size="md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </x-formal-button>
                </a>
                <x-formal-button
                    type="submit"
                    variant="primary"
                    size="md"
                    id="submit-btn"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Submit Feedback
                </x-formal-button>
            </div>
        </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('feedback-form');
    const submitBtn = document.getElementById('submit-btn');

    // Handle radio button selection styling
    const radioGroups = document.querySelectorAll('input[type="radio"]');
    radioGroups.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove selected styling from siblings
            const siblings = document.querySelectorAll(`input[name="${this.name}"]`);
            siblings.forEach(sibling => {
                sibling.closest('label').classList.remove('ring-2', 'ring-indigo-500', 'border-indigo-500', 'bg-indigo-50');
            });

            // Add selected styling to current
            this.closest('label').classList.add('ring-2', 'ring-indigo-500', 'border-indigo-500', 'bg-indigo-50');
        });
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Set loading state on the button
        submitBtn.setAttribute('data-loading', 'true');

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Create FormData for file upload
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('type', document.querySelector('input[name="type"]:checked')?.value);
        formData.append('priority', document.querySelector('input[name="priority"]:checked')?.value);
        formData.append('title', document.getElementById('title').value);
        formData.append('description', document.getElementById('description').value);

        // Add images if selected
        const imagesInput = document.getElementById('images');
        if (imagesInput.files.length > 0) {
            for (let i = 0; i < imagesInput.files.length; i++) {
                formData.append('images[]', imagesInput.files[i]);
            }
        }

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ToastNotification.success(data.message);
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1500);
            } else {
                ToastNotification.error(data.message || 'An error occurred while submitting feedback.');
                submitBtn.removeAttribute('data-loading');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ToastNotification.error('An error occurred while submitting feedback. Please try again.');
            submitBtn.removeAttribute('data-loading');
        });
    });
});

// Multiple image upload handling functions
let selectedImages = [];

function handleMultipleImageUpload(input) {
    const files = Array.from(input.files);

    // Limit to maximum 5 images
    if (files.length > 5) {
        ToastNotification.error('You can only upload up to 5 images at once');
        input.value = '';
        return;
    }

    // Validate each file
    const validFiles = [];
    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            ToastNotification.error(`Image "${file.name}" is too large. Maximum size is 5MB.`);
            continue;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            ToastNotification.error(`"${file.name}" is not a valid image file. Please select JPEG, PNG, GIF, or WEBP files.`);
            continue;
        }

        validFiles.push(file);
    }

    if (validFiles.length === 0) {
        input.value = '';
        return;
    }

    // Update selected images
    selectedImages = validFiles;
    updateImagePreviews();
}

function updateImagePreviews() {
    const previewGrid = document.getElementById('preview-grid');
    const imagePreviews = document.getElementById('image-previews');
    const imageCount = document.getElementById('image-count');
    const uploadArea = document.getElementById('image-upload-area');

    // Clear existing previews
    previewGrid.innerHTML = '';

    if (selectedImages.length === 0) {
        imagePreviews.classList.add('hidden');
        uploadArea.classList.remove('hidden');
        return;
    }

    // Show previews
    imagePreviews.classList.remove('hidden');
    uploadArea.classList.add('hidden');
    imageCount.textContent = selectedImages.length;

    // Create preview for each image
    selectedImages.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'relative group';
            previewDiv.innerHTML = `
                <div class="relative">
                    <img src="${e.target.result}"
                         alt="Preview ${index + 1}"
                         class="w-full h-24 object-cover rounded-lg border border-gray-300">
                    <button type="button"
                            onclick="removeImage(${index})"
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100">
                        ×
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1 truncate" title="${file.name}">${file.name}</p>
            `;
            previewGrid.appendChild(previewDiv);
        };
        reader.readAsDataURL(file);
    });
}

function removeImage(index) {
    selectedImages.splice(index, 1);
    updateImagePreviews();

    // Update the file input
    const input = document.getElementById('images');
    const dt = new DataTransfer();
    selectedImages.forEach(file => dt.items.add(file));
    input.files = dt.files;
}

function clearAllImages() {
    selectedImages = [];
    document.getElementById('images').value = '';
    updateImagePreviews();
}

// Drag and drop functionality for multiple images
const uploadArea = document.getElementById('image-upload-area');
const imagesInput = document.getElementById('images');

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('border-indigo-400', 'bg-indigo-50');
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-indigo-400', 'bg-indigo-50');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-indigo-400', 'bg-indigo-50');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        imagesInput.files = files;
        handleMultipleImageUpload(imagesInput);
    }
});
</script>
    </div>
</div>
@endsection
