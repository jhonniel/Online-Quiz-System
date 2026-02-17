@extends('layouts.admin')

@section('page-title', 'Send Notification')

@section('content')
<div class="mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
    <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Send Notification</h1>
                    <p class="text-sm text-gray-600 mt-1">Send notifications to specific users or all users</p>
                </div>
                <a href="{{ route('admin.notifications.index') }}"
                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Notifications
                </a>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.notifications.store') }}" class="p-6 space-y-6">
            @csrf

            <!-- Quick Templates -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quick Templates</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <button type="button" onclick="useTemplate('maintenance')"
                            class="p-3 text-left border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">🔧</span>
                            <div>
                                <div class="font-medium text-gray-900">System Maintenance</div>
                                <div class="text-sm text-gray-500">Scheduled maintenance notification</div>
                            </div>
                        </div>
                    </button>
                    <button type="button" onclick="useTemplate('update')"
                            class="p-3 text-left border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">🆕</span>
                            <div>
                                <div class="font-medium text-gray-900">New Features</div>
                                <div class="text-sm text-gray-500">Announce new features or updates</div>
                            </div>
                        </div>
                    </button>
                    <button type="button" onclick="useTemplate('bug')"
                            class="p-3 text-left border border-gray-200 rounded-lg hover:bg-red-50 hover:border-red-300 transition-colors">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">⚠️</span>
                            <div>
                                <div class="font-medium text-gray-900">Bug Alert</div>
                                <div class="text-sm text-gray-500">Report known issues or bugs</div>
                            </div>
                        </div>
                    </button>
                    <button type="button" onclick="useTemplate('reminder')"
                            class="p-3 text-left border border-gray-200 rounded-lg hover:bg-yellow-50 hover:border-yellow-300 transition-colors">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">⏰</span>
                            <div>
                                <div class="font-medium text-gray-900">Reminder</div>
                                <div class="text-sm text-gray-500">Send important reminders</div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Notification Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Notification Type</label>
                <select id="type" name="type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Select notification type</option>
                    <option value="admin_notification" {{ old('type') === 'admin_notification' ? 'selected' : '' }}>📢 Admin Notification</option>
                    <option value="system_update" {{ old('type') === 'system_update' ? 'selected' : '' }}>🔄 System Update</option>
                    <option value="bug_alert" {{ old('type') === 'bug_alert' ? 'selected' : '' }}>🐛 Bug Alert</option>
                    <option value="custom" {{ old('type') === 'custom' ? 'selected' : '' }}>💬 Custom Message</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required maxlength="255"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Enter notification title">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Message -->
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                <textarea id="message" name="message" rows="4" required maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                          placeholder="Enter notification message">{{ old('message') }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Maximum 1000 characters</p>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Recipients -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                <div class="space-y-4">
                    <!-- Send to All Users -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <input type="radio" id="send_to_all" name="recipient_type" value="all"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <label for="send_to_all" class="ml-3 text-sm font-medium text-gray-900">
                                Send to All Users
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">This will send the notification to all registered users in the system.</p>
                    </div>

                    <!-- Send to Specific Users -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <input type="radio" id="send_to_specific" name="recipient_type" value="specific"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <label for="send_to_specific" class="ml-3 text-sm font-medium text-gray-900">
                                Send to Specific Users
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">Select specific users to receive this notification.</p>

                        <!-- User Selection -->
                        <div id="user_selection" class="mt-4 hidden">
                            <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-md">
                                @foreach($users as $user)
                                    <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <div class="ml-3 flex items-center">
                                            @if($user->profile_picture)
                                                <img class="h-8 w-8 rounded-full" src="{{ $user->getProfilePictureUrl() }}" alt="{{ $user->name }}">
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-gray-700">{{ substr($user->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <button type="button" onclick="selectAllUsers()" class="text-sm text-indigo-600 hover:text-indigo-800">Select All</button>
                                <button type="button" onclick="deselectAllUsers()" class="text-sm text-gray-600 hover:text-gray-800">Deselect All</button>
                            </div>
                        </div>
                    </div>
                </div>
                @error('user_ids')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Preview -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900" id="preview_title">Notification Title</p>
                            <p class="text-sm text-gray-600 mt-1" id="preview_message">Notification message will appear here...</p>
                            <p class="text-xs text-gray-400 mt-1">Just now</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.notifications.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Send Notification
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Handle recipient type change
document.querySelectorAll('input[name="recipient_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const userSelection = document.getElementById('user_selection');
        if (this.value === 'specific') {
            userSelection.classList.remove('hidden');
        } else {
            userSelection.classList.add('hidden');
        }
    });
});

// Update preview in real-time
document.getElementById('title').addEventListener('input', function() {
    document.getElementById('preview_title').textContent = this.value || 'Notification Title';
});

document.getElementById('message').addEventListener('input', function() {
    document.getElementById('preview_message').textContent = this.value || 'Notification message will appear here...';
});

// User selection helpers
function selectAllUsers() {
    document.querySelectorAll('input[name="user_ids[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllUsers() {
    document.querySelectorAll('input[name="user_ids[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Template functions
function useTemplate(templateType) {
    const templates = {
        maintenance: {
            type: 'system_update',
            title: '🔧 Scheduled System Maintenance',
            message: 'We will be performing scheduled system maintenance on [DATE] from [TIME] to [TIME]. During this time, the system may be temporarily unavailable. We apologize for any inconvenience and appreciate your patience.'
        },
        update: {
            type: 'system_update',
            title: '🆕 New Features Available',
            message: 'We\'re excited to announce new features and improvements to the system! Check out the latest updates and enhancements that will make your experience even better.'
        },
        bug: {
            type: 'bug_alert',
            title: '⚠️ Known Issue Alert',
            message: 'We are currently aware of an issue affecting [SPECIFIC FUNCTIONALITY]. Our team is working to resolve this as quickly as possible. We apologize for any inconvenience.'
        },
        reminder: {
            type: 'admin_notification',
            title: '⏰ Important Reminder',
            message: 'This is a friendly reminder about [IMPORTANT INFORMATION]. Please take note of this information and take any necessary action.'
        }
    };

    const template = templates[templateType];
    if (template) {
        document.getElementById('type').value = template.type;
        document.getElementById('title').value = template.title;
        document.getElementById('message').value = template.message;

        // Update preview
        document.getElementById('preview_title').textContent = template.title;
        document.getElementById('preview_message').textContent = template.message;
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const recipientType = document.querySelector('input[name="recipient_type"]:checked');
    if (!recipientType) {
        e.preventDefault();
        alert('Please select a recipient type.');
        return;
    }

    if (recipientType.value === 'specific') {
        const selectedUsers = document.querySelectorAll('input[name="user_ids[]"]:checked');
        if (selectedUsers.length === 0) {
            e.preventDefault();
            alert('Please select at least one user.');
            return;
        }
    }
});
</script>
@endsection
