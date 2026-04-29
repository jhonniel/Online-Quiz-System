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
                    <!-- Send to All Roles -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <input type="radio" id="send_to_all_roles" name="recipient_type" value="all_roles"
                                   {{ old('recipient_type') === 'all_roles' ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <label for="send_to_all_roles" class="ml-3 text-sm font-medium text-gray-900">
                                Send to All Roles
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">This will send the notification to all users across all available roles.</p>
                    </div>

                    <!-- Send to Specific Roles -->
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <input type="radio" id="send_to_roles" name="recipient_type" value="specific_roles"
                                   {{ old('recipient_type') === 'specific_roles' ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <label for="send_to_roles" class="ml-3 text-sm font-medium text-gray-900">
                                Send to Specific Roles
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">Select one or more roles to receive this notification.</p>

                        <div id="role_selection" class="mt-4 hidden">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($roles as $role)
                                    <label class="flex items-center px-3 py-2 border border-gray-200 rounded-md bg-white">
                                        <input type="checkbox" name="roles[]" value="{{ $role }}"
                                               {{ in_array($role, old('roles', []), true) ? 'checked' : '' }}
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-800">{{ $roleLabels[$role] ?? ucfirst($role) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('roles')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Send to Specific Users -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <input type="radio" id="send_to_specific_users" name="recipient_type" value="specific_users"
                                   {{ old('recipient_type') === 'specific_users' ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <label for="send_to_specific_users" class="ml-3 text-sm font-medium text-gray-900">
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
                                               {{ in_array((string) $user->id, array_map('strval', old('user_ids', [])), true) ? 'checked' : '' }}
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

            @php
                $usersByRole = $users->groupBy('role')->map(fn ($items) => $items->count());
            @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Summary</label>
                <div id="recipient_summary_box" class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <p id="recipient_summary" class="text-sm text-indigo-800 font-medium">
                        This will notify: 0 users
                    </p>
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

<script type="application/json" id="users-data-json">{!! json_encode($users->map(fn($u) => ['id' => (int) $u->id, 'role' => $u->role])->values()) !!}</script>
<script type="application/json" id="role-labels-json">{!! json_encode($roleLabels) !!}</script>
<script type="application/json" id="users-by-role-count-json">{!! json_encode($usersByRole) !!}</script>
<script>
const usersData = JSON.parse(document.getElementById('users-data-json').textContent || '[]');
const roleLabels = JSON.parse(document.getElementById('role-labels-json').textContent || '{}');
const usersByRoleCount = JSON.parse(document.getElementById('users-by-role-count-json').textContent || '{}');

// Handle recipient type change
function toggleRecipientSections() {
    const selected = document.querySelector('input[name="recipient_type"]:checked');
    const roleSelection = document.getElementById('role_selection');
    const userSelection = document.getElementById('user_selection');
    const selectedType = selected ? selected.value : null;

    roleSelection.classList.toggle('hidden', selectedType !== 'specific_roles');
    userSelection.classList.toggle('hidden', selectedType !== 'specific_users');

    updateRecipientSummary();
}

document.querySelectorAll('input[name="recipient_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        toggleRecipientSections();
    });
});
toggleRecipientSections();

document.querySelectorAll('input[name="roles[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', updateRecipientSummary);
});

document.querySelectorAll('input[name="user_ids[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', updateRecipientSummary);
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
    updateRecipientSummary();
}

function updateRecipientSummary() {
    const summaryEl = document.getElementById('recipient_summary');
    const summaryBoxEl = document.getElementById('recipient_summary_box');
    const selectedRecipientType = document.querySelector('input[name="recipient_type"]:checked');
    const setSummaryState = (state) => {
        summaryBoxEl.classList.remove(
            'bg-indigo-50', 'border-indigo-200',
            'bg-green-50', 'border-green-200',
            'bg-amber-50', 'border-amber-200',
            'bg-red-50', 'border-red-200'
        );
        summaryEl.classList.remove(
            'text-indigo-800',
            'text-green-800',
            'text-amber-800',
            'text-red-800'
        );

        if (state === 'success') {
            summaryBoxEl.classList.add('bg-green-50', 'border-green-200');
            summaryEl.classList.add('text-green-800');
        } else if (state === 'warning') {
            summaryBoxEl.classList.add('bg-amber-50', 'border-amber-200');
            summaryEl.classList.add('text-amber-800');
        } else if (state === 'error') {
            summaryBoxEl.classList.add('bg-red-50', 'border-red-200');
            summaryEl.classList.add('text-red-800');
        } else {
            summaryBoxEl.classList.add('bg-indigo-50', 'border-indigo-200');
            summaryEl.classList.add('text-indigo-800');
        }
    };

    if (!selectedRecipientType) {
        summaryEl.textContent = 'Select a recipient option to preview audience size.';
        setSummaryState('warning');
        return;
    }

    const recipientType = selectedRecipientType.value;
    if (recipientType === 'all_roles') {
        const roleNames = Object.keys(usersByRoleCount);
        const totalUsers = roleNames.reduce((total, role) => total + Number(usersByRoleCount[role] || 0), 0);
        const labelText = roleNames.map(role => roleLabels[role] || role.charAt(0).toUpperCase() + role.slice(1)).join(', ');
        summaryEl.textContent = `This will notify: ${totalUsers} users (${labelText || 'No roles'})`;
        setSummaryState(totalUsers > 0 ? 'success' : 'warning');
        return;
    }

    if (recipientType === 'specific_roles') {
        const selectedRoles = Array.from(document.querySelectorAll('input[name="roles[]"]:checked')).map(input => input.value);
        const totalUsers = selectedRoles.reduce((total, role) => total + Number(usersByRoleCount[role] || 0), 0);
        const labelText = selectedRoles.map(role => roleLabels[role] || role.charAt(0).toUpperCase() + role.slice(1)).join(', ');
        if (selectedRoles.length === 0) {
            summaryEl.textContent = 'No roles selected yet. Please choose at least one role.';
            setSummaryState('warning');
        } else if (totalUsers === 0) {
            summaryEl.textContent = `Selected roles (${labelText}) currently have no users.`;
            setSummaryState('error');
        } else {
            summaryEl.textContent = `This will notify: ${totalUsers} users (${labelText})`;
            setSummaryState('success');
        }
        return;
    }

    const selectedUsers = Array.from(document.querySelectorAll('input[name="user_ids[]"]:checked')).map(input => Number(input.value));
    const selectedUsersSet = new Set(selectedUsers);
    const roleCountMap = {};

    usersData.forEach(user => {
        if (selectedUsersSet.has(Number(user.id))) {
            roleCountMap[user.role] = (roleCountMap[user.role] || 0) + 1;
        }
    });

    const roleBreakdown = Object.keys(roleCountMap)
        .map(role => `${roleLabels[role] || role}: ${roleCountMap[role]}`)
        .join(', ');

    if (selectedUsers.length === 0) {
        summaryEl.textContent = 'No specific users selected yet. Please choose at least one user.';
        setSummaryState('warning');
    } else {
        summaryEl.textContent = `This will notify: ${selectedUsers.length} users${roleBreakdown ? ` (${roleBreakdown})` : ''}`;
        setSummaryState('success');
    }
}
updateRecipientSummary();

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

    if (recipientType.value === 'specific_roles') {
        const selectedRoles = document.querySelectorAll('input[name="roles[]"]:checked');
        if (selectedRoles.length === 0) {
            e.preventDefault();
            alert('Please select at least one role.');
            return;
        }
    }

    if (recipientType.value === 'specific_users') {
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
