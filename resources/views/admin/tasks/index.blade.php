@extends('layouts.admin')

@section('page-title', $type === 'personal' ? 'My Tasks' : 'Group Tasks')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Task Assign</span>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">{{ $type === 'personal' ? 'My Tasks' : 'Group Tasks' }}</span>
        </div>
    </li>
@endsection

@section('content')
<!-- Define functions inline before Alpine initializes -->
<script>
    // Define taskManager function globally before Alpine.js initializes
    function taskManager() {
        return {
            view: '{{ $view ?? 'board' }}',
            currentTask: null,
            draggedTask: null,
            modalOpen: false,
            tasksData: [],
            searchQuery: '',
            
            openCreateModal() {
                console.log('openCreateModal called');
                // Get the modal element and its Alpine component
                const modal = document.getElementById('task-modal');
                console.log('Modal element:', modal);
                
                if (!modal) {
                    console.error('Modal element not found');
                    alert('Modal not found. Please refresh the page.');
                    return;
                }
                
                // Show modal first
                modal.classList.remove('hidden');
                modal.style.display = 'block';
                modal.style.zIndex = '9999';
                
                // Try to get Alpine component and call openModal
                if (window.Alpine && Alpine.$data) {
                    try {
                        const modalComponent = Alpine.$data(modal);
                        console.log('Alpine component:', modalComponent);
                        if (modalComponent && typeof modalComponent.openModal === 'function') {
                            console.log('Calling openModal with null');
                            modalComponent.openModal(null);
                            return;
                        } else {
                            console.warn('openModal method not found on component');
                        }
                    } catch (e) {
                        console.error('Error getting Alpine component:', e);
                    }
                } else {
                    console.warn('Alpine not available');
                }
                
                // Fallback: Dispatch event
                console.log('Dispatching open-task-modal event');
                setTimeout(() => {
                    const event = new CustomEvent('open-task-modal', { detail: { task: null } });
                    window.dispatchEvent(event);
                }, 200);
            },
            
            openEditModal(task) {
                // Find full task data from tasksData
                const fullTask = this.tasksData.find(t => t.id === task.id) || task;
                const modal = document.getElementById('task-modal');
                if (modal) {
                    if (window.Alpine && Alpine.$data) {
                        const modalComponent = Alpine.$data(modal);
                        if (modalComponent && modalComponent.openModal) {
                            modalComponent.openModal(fullTask);
                            return;
                        }
                    }
                    window.dispatchEvent(new CustomEvent('open-task-modal', { detail: { task: fullTask } }));
                }
            },
            
            closeModal() {
                this.modalOpen = false;
                this.currentTask = null;
            },
            
            handleDragStart(event, task) {
                this.draggedTask = task;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', task.id);
                // Add visual feedback
                event.currentTarget.style.opacity = '0.5';
            },
            
            handleDrop(event, newStatus) {
                event.preventDefault();
                event.stopPropagation();
                
                if (!this.draggedTask) return;
                
                const taskId = this.draggedTask.id;
                const oldStatus = this.draggedTask.status;
                
                if (oldStatus === newStatus) {
                    this.draggedTask = null;
                    // Reset opacity
                    document.querySelectorAll('[draggable="true"]').forEach(el => {
                        el.style.opacity = '1';
                    });
                    return;
                }
                
                // Update task status
                fetch(`/admin/tasks/${taskId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error updating task: ' + (data.message || 'Unknown error'));
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the task');
                    location.reload();
                });
                
                this.draggedTask = null;
            },
            
            async deleteTask(taskId) {
                if (!confirm('Are you sure you want to delete this task?')) return;
                
                try {
                    const response = await fetch(`/admin/tasks/${taskId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete task'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the task');
                }
            }
        }
    }

    // Define taskModal function globally before Alpine.js initializes
    function taskModal() {
        return {
            currentTask: null,
            formData: {
                title: '',
                description: '',
                due_date: '',
                due_time: '',
                assigned_users: [],
                type: '{{ $type }}'
            },
            newComment: '',
            selectedFile: null,
            
            openModal(task) {
                console.log('taskModal openModal called with:', task);
                const taskType = '{{ $type }}'; // 'personal' or 'group'
                
                if (task) {
                    // Editing existing task - ensure all properties exist
                    this.currentTask = {
                        id: task.id,
                        title: task.title || '',
                        description: task.description || '',
                        due_date: task.due_date || null,
                        type: task.type || taskType,
                        attachments: Array.isArray(task.attachments) ? task.attachments : [],
                        comments: Array.isArray(task.comments) ? task.comments : [],
                        assignments: Array.isArray(task.assignments) ? task.assignments : []
                    };
                    this.formData = {
                        title: task.title || '',
                        description: task.description || '',
                        due_date: task.due_date ? new Date(task.due_date).toISOString().split('T')[0] : '',
                        due_time: task.due_date ? new Date(task.due_date).toTimeString().slice(0, 5) : '',
                        assigned_users: task.assignments ? task.assignments.map(a => a.user_id) : [],
                        type: task.type || taskType
                    };
                } else {
                    // Creating new task - set currentTask to null explicitly
                    this.currentTask = null;
                    this.formData = {
                        title: '',
                        description: '',
                        due_date: '',
                        due_time: '',
                        assigned_users: [],
                        type: taskType
                    };
                }
                
                console.log('Opening modal with type:', this.formData.type, 'currentTask:', this.currentTask);
                const modal = document.getElementById('task-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'block';
                    console.log('Modal shown');
                    // Force a reflow to ensure visibility
                    modal.offsetHeight;
                } else {
                    console.error('Modal element not found');
                }
            },
            
            closeModal() {
                const modal = document.getElementById('task-modal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.style.display = 'none';
                }
                this.currentTask = null;
                const taskType = '{{ $type }}'; // Preserve the page type
                this.formData = {
                    title: '',
                    description: '',
                    due_date: '',
                    due_time: '',
                    assigned_users: [],
                    type: taskType
                };
                this.newComment = '';
                this.selectedFile = null;
                // Reset file input
                const fileInput = document.getElementById('task-file-input');
                if (fileInput) fileInput.value = '';
            },
            
            handleFileSelect(event) {
                this.selectedFile = event.target.files[0];
            },
            
            async saveTask() {
                // Validate required fields
                if (!this.formData.title || this.formData.title.trim() === '') {
                    alert('Please enter a task title');
                    return;
                }
                
                if (!this.formData.type) {
                    alert('Task type is missing. Please refresh the page and try again.');
                    return;
                }
                
                const url = this.currentTask 
                    ? `/admin/tasks/${this.currentTask.id}`
                    : '/admin/tasks';
                const method = this.currentTask ? 'PUT' : 'POST';
                
                const requestData = {
                    title: this.formData.title.trim(),
                    description: this.formData.description || '',
                    type: this.formData.type,
                };
                
                if (this.formData.due_date) {
                    requestData.due_date = this.formData.due_date;
                    if (this.formData.due_time) {
                        requestData.due_time = this.formData.due_time;
                    }
                }
                
                console.log('Saving task with data:', requestData);
                
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(requestData)
                    });
                    
                    const data = await response.json();
                    console.log('Response:', data);
                    
                    if (data.success) {
                        const taskId = data.task.id;
                        
                        // Upload file if selected (for both new and existing tasks)
                        if (this.selectedFile) {
                            const uploadSuccess = await this.uploadFile(taskId);
                            if (!uploadSuccess) {
                                console.warn('File upload failed, but task was created');
                            }
                        }
                        
                        // Assign users if group task (only for new tasks or if updating)
                        if (this.formData.type === 'group' && this.formData.assigned_users.length > 0) {
                            if (!this.currentTask || (this.currentTask && JSON.stringify(this.formData.assigned_users.sort()) !== JSON.stringify((this.currentTask.assignments || []).map(a => a.user_id).sort()))) {
                                await this.assignUsers(taskId);
                            }
                        }
                        
                        location.reload();
                    } else {
                        // Show validation errors if any
                        let errorMessage = data.message || 'Failed to save task';
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat().join('\n');
                            errorMessage = errorMessage + '\n\n' + errorList;
                        }
                        alert('Error: ' + errorMessage);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while saving the task: ' + error.message);
                }
            },
            
            async uploadFile(taskId) {
                if (!this.selectedFile) return false;
                
                const formData = new FormData();
                formData.append('file', this.selectedFile);
                
                try {
                    const response = await fetch(`/admin/tasks/${taskId}/attachments`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && this.currentTask) {
                        // Add attachment to current task's attachments array
                        if (!this.currentTask.attachments) {
                            this.currentTask.attachments = [];
                        }
                        this.currentTask.attachments.push(data.attachment);
                    }
                    
                    return data.success;
                } catch (error) {
                    console.error('Error uploading file:', error);
                    return false;
                }
            },
            
            async assignUsers(taskId) {
                const roles = this.formData.assigned_users.map(() => 'assignee');
                
                try {
                    await fetch(`/admin/tasks/${taskId}/assign-users`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            user_ids: this.formData.assigned_users,
                            roles: roles
                        })
                    });
                } catch (error) {
                    console.error('Error assigning users:', error);
                }
            },
            
            async addComment() {
                if (!this.newComment.trim() || !this.currentTask) return;
                
                try {
                    const response = await fetch(`/admin/tasks/${this.currentTask.id}/comments`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            comment: this.newComment
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        if (!this.currentTask.comments) {
                            this.currentTask.comments = [];
                        }
                        this.currentTask.comments.push(data.comment);
                        this.newComment = '';
                    }
                } catch (error) {
                    console.error('Error adding comment:', error);
                }
            },
            
            isImage(fileType) {
                if (!fileType) return false;
                return ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'].includes(fileType.toLowerCase());
            },
            
            hasAttachments() {
                return this.currentTask !== null && 
                       this.currentTask !== undefined && 
                       this.currentTask.attachments && 
                       Array.isArray(this.currentTask.attachments) && 
                       this.currentTask.attachments.length > 0;
            },
            
            hasCurrentTask() {
                return this.currentTask !== null && this.currentTask !== undefined;
            },
            
            async deleteAttachment(attachmentId) {
                if (!confirm('Are you sure you want to delete this attachment?')) return;
                
                try {
                    const response = await fetch(`/admin/tasks/attachments/${attachmentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.currentTask.attachments = this.currentTask.attachments.filter(a => a.id !== attachmentId);
                    }
                } catch (error) {
                    console.error('Error deleting attachment:', error);
                }
            }
        }
    }

    // Set up event listener when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, setting up modal event listener');
        
        // Wait a bit for Alpine to initialize
        setTimeout(() => {
            window.addEventListener('open-task-modal', function(e) {
                console.log('open-task-modal event received:', e.detail);
                const modal = document.getElementById('task-modal');
                if (modal) {
                    // Show modal first
                    modal.classList.remove('hidden');
                    modal.style.display = 'block';
                    modal.style.zIndex = '9999';
                    
                    // Try to get Alpine component
                    if (window.Alpine && Alpine.$data) {
                        try {
                            const modalComponent = Alpine.$data(modal);
                            if (modalComponent && modalComponent.openModal) {
                                modalComponent.openModal(e.detail.task);
                            } else {
                                console.warn('Alpine component or openModal method not available');
                                // Fallback: manually trigger form reset
                                const form = modal.querySelector('form');
                                if (form) form.reset();
                            }
                        } catch (error) {
                            console.error('Error accessing Alpine component:', error);
                        }
                    } else {
                        console.warn('Alpine not available yet');
                    }
                } else {
                    console.error('Modal element not found');
                }
            });
        }, 500);
    });
</script>

<div class="space-y-6" x-data="taskManager()" x-init="tasksData = @js($tasksJson ?? [])">
    <!-- Modern Header Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Project Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $type === 'personal' ? 'My Tasks' : 'Group Tasks' }}</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ $type === 'personal' ? 'Manage your personal tasks' : 'Collaborate on team tasks' }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    @if($type === 'group')
                    <div class="flex -space-x-2">
                        @foreach($users->take(5) as $user)
                        <img src="{{ $user->getProfilePictureUrl() }}" 
                             alt="{{ $user->name }}"
                             class="w-8 h-8 rounded-full border-2 border-white">
                        @endforeach
                        @if($users->count() > 5)
                        <span class="w-8 h-8 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-xs text-gray-600 font-medium">+{{ $users->count() - 5 }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="px-6 border-b border-gray-200">
            <div class="flex space-x-1">
                <button @click="view = 'board'" 
                        :class="view === 'board' ? 'border-b-2 border-yellow-400 text-gray-900 font-medium' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-3 text-sm transition-colors">
                    Board
                </button>
                <button @click="view = 'list'" 
                        :class="view === 'list' ? 'border-b-2 border-yellow-400 text-gray-900 font-medium' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-3 text-sm transition-colors">
                    List
                </button>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="px-6 py-3 bg-gray-50 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center space-x-3 flex-1 min-w-[300px]">
                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           x-model="searchQuery"
                           placeholder="Search task..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                
                <!-- Sort -->
                <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400">
                    <option>Sort by: Stage</option>
                    <option>Sort by: Due Date</option>
                    <option>Sort by: Priority</option>
                </select>
                
                <!-- Filter -->
                <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-100 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter
                </button>
            </div>
            
            <!-- Add Task Button -->
            <button @click="openCreateModal()" 
                    type="button"
                    class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-medium rounded-lg shadow-sm transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add New Task
            </button>
        </div>
    </div>

    <!-- Board View -->
    <div x-show="view === 'board'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- To Do Column -->
        <div class="bg-gradient-to-b from-pink-50 to-white rounded-lg border border-pink-200">
            <div class="p-4 border-b border-pink-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-pink-700">To Do</h2>
                    <span class="bg-pink-100 text-pink-700 text-xs font-semibold px-2.5 py-1 rounded-full">{{ $tasksByStatus['todo']->count() }}</span>
                </div>
            </div>
            <div class="p-4 space-y-3 min-h-[500px] transition-colors duration-200" 
                 data-status="todo"
                 @drop="handleDrop($event, 'todo')" 
                 @dragover.prevent
                 @dragenter.prevent
                 :class="draggedTask ? 'bg-pink-50 border-2 border-pink-300 border-dashed rounded-lg' : ''">
                @foreach($tasksByStatus['todo'] as $task)
                    @include('admin.tasks.partials.task-card', ['task' => $task, 'type' => $type])
                @endforeach
            </div>
        </div>

        <!-- In Progress Column -->
        <div class="bg-gradient-to-b from-orange-50 to-white rounded-lg border border-orange-200">
            <div class="p-4 border-b border-orange-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-orange-700">In Progress</h2>
                    <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-2.5 py-1 rounded-full">{{ $tasksByStatus['in_progress']->count() }}</span>
                </div>
            </div>
            <div class="p-4 space-y-3 min-h-[500px] transition-colors duration-200" 
                 data-status="in_progress"
                 @drop="handleDrop($event, 'in_progress')" 
                 @dragover.prevent
                 @dragenter.prevent
                 :class="draggedTask ? 'bg-orange-50 border-2 border-orange-300 border-dashed rounded-lg' : ''">
                @foreach($tasksByStatus['in_progress'] as $task)
                    @include('admin.tasks.partials.task-card', ['task' => $task, 'type' => $type])
                @endforeach
            </div>
        </div>

        <!-- Done Column -->
        <div class="bg-gradient-to-b from-purple-50 to-white rounded-lg border border-purple-200">
            <div class="p-4 border-b border-purple-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-purple-700">Completed</h2>
                    <span class="bg-purple-100 text-purple-700 text-xs font-semibold px-2.5 py-1 rounded-full">{{ $tasksByStatus['done']->count() }}</span>
                </div>
            </div>
            <div class="p-4 space-y-3 min-h-[500px] transition-colors duration-200" 
                 data-status="done"
                 @drop="handleDrop($event, 'done')" 
                 @dragover.prevent
                 @dragenter.prevent
                 :class="draggedTask ? 'bg-purple-50 border-2 border-purple-300 border-dashed rounded-lg' : ''">
                @foreach($tasksByStatus['done'] as $task)
                    @include('admin.tasks.partials.task-card', ['task' => $task, 'type' => $type])
                @endforeach
            </div>
        </div>
    </div>

    <!-- List View -->
    <div x-show="view === 'list'" class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        @if($type === 'group')
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tasks as $task)
                        @include('admin.tasks.partials.task-row', ['task' => $task, 'type' => $type])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Task Modal -->
    @include('admin.tasks.partials.task-modal', ['type' => $type, 'users' => $users ?? collect()])
</div>
@endsection
