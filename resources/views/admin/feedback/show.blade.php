@extends('layouts.admin')

@section('page-title', 'Feedback Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('admin.feedback.index') }}" class="mr-4">
                    <svg class="w-6 h-6 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Feedback Details</h1>
                    <p class="mt-2 text-gray-600">Review and respond to user feedback.</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <form method="POST" action="{{ route('admin.feedback.destroy', $feedback) }}" onsubmit="return confirmFeedbackAction('delete', this)">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Feedback Details -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="mb-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $feedback->title }}</h2>
                                <div class="flex items-center space-x-3 mb-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $feedback->type_badge_class }}">
                                        {{ $feedback->type_icon }} {{ ucfirst(str_replace('_', ' ', $feedback->type)) }}
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $feedback->priority_badge_class }}">
                                        {{ $feedback->priority_icon }} {{ ucfirst($feedback->priority) }} Priority
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $feedback->status_badge_class }}">
                                        {{ ucfirst(str_replace('_', ' ', $feedback->status)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right text-sm text-gray-500">
                                <div>Submitted {{ $feedback->created_at->format('M d, Y \a\t g:i A') }}</div>
                                <div>{{ $feedback->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Description</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $feedback->description }}</p>
                        </div>
                    </div>

                    @if($feedback->hasImage())
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">
                                Attached Images ({{ $feedback->image_count }})
                            </h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach($feedback->image_urls as $index => $imageUrl)
                                        <div class="relative group">
                                            <img src="{{ $imageUrl }}"
                                                 alt="Feedback Image {{ $index + 1 }}"
                                                 class="w-full h-32 object-cover rounded-lg border border-gray-300 cursor-pointer hover:opacity-90 transition-opacity"
                                                 onclick="openImageModal('{{ $imageUrl }}')">
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 rounded-lg flex items-center justify-center">
                                                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 text-sm text-gray-600 text-center">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Click any image to view full size
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($feedback->admin_response)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Admin Response</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-blue-800 whitespace-pre-wrap">{{ $feedback->admin_response }}</p>
                                @if($feedback->admin_responded_at)
                                    <p class="mt-2 text-sm text-blue-600">
                                        Responded {{ $feedback->admin_responded_at->format('M d, Y \a\t g:i A') }}
                                        @if($feedback->assignedAdmin)
                                            by {{ $feedback->assignedAdmin->name }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Admin Response Form -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Update Feedback</h3>
                    <form id="feedback-update-form" action="{{ route('admin.feedback.update', $feedback) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="pending" {{ $feedback->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_review" {{ $feedback->status == 'in_review' ? 'selected' : '' }}>In Review</option>
                                    <option value="in_progress" {{ $feedback->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $feedback->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="rejected" {{ $feedback->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>

                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                                <select name="assigned_to" id="assigned_to" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Unassigned</option>
                                    @foreach(\App\Models\User::where('role', 'admin')->get() as $admin)
                                        <option value="{{ $admin->id }}" {{ $feedback->assigned_to == $admin->id ? 'selected' : '' }}>
                                            {{ $admin->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="admin_response" class="block text-sm font-medium text-gray-700 mb-1">Admin Response</label>
                            <textarea name="admin_response"
                                      id="admin_response"
                                      rows="4"
                                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                      placeholder="Provide a response to the user...">{{ $feedback->admin_response }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    id="update-btn"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Update Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="flex-shrink-0 h-12 w-12">
                            @if($feedback->user->profile_picture)
                                <img class="h-12 w-12 rounded-full" src="{{ Storage::url($feedback->user->profile_picture) }}" alt="{{ $feedback->user->name }}">
                            @else
                                <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-lg font-medium text-gray-700">{{ substr($feedback->user->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $feedback->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $feedback->user->email }}</div>
                            @if($feedback->user->university)
                                <div class="text-sm text-gray-500">{{ $feedback->user->university->name }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Member since:</span>
                            <span class="text-gray-900">{{ $feedback->user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total feedback:</span>
                            <span class="text-gray-900">{{ $feedback->user->feedbacks()->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback Timeline -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Timeline</h3>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Feedback submitted</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $feedback->created_at->format('M d, Y g:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            @if($feedback->status !== 'pending')
                                <li>
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Status updated to {{ ucfirst(str_replace('_', ' ', $feedback->status)) }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    {{ $feedback->updated_at->format('M d, Y g:i A') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif

                            @if($feedback->admin_response)
                                <li>
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Admin responded</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    {{ $feedback->admin_responded_at->format('M d, Y g:i A') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('feedback-update-form');
    const updateBtn = document.getElementById('update-btn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const originalText = updateBtn.innerHTML;
        updateBtn.disabled = true;
        updateBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Updating...
        `;

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(this.action, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                status: document.getElementById('status').value,
                assigned_to: document.getElementById('assigned_to').value,
                admin_response: document.getElementById('admin_response').value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ToastNotification.success(data.message);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                ToastNotification.error(data.message || 'An error occurred while updating feedback.');
                updateBtn.disabled = false;
                updateBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ToastNotification.error('An error occurred while updating feedback. Please try again.');
            updateBtn.disabled = false;
            updateBtn.innerHTML = originalText;
        });
    });
});

function confirmFeedbackAction(action, button) {
    event.preventDefault();

    if (confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) {
        button.closest('form').submit();
    }

    return false;
}

function openImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside the image
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>
@endsection
