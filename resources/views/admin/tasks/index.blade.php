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

@push('styles')
<script>
// Define taskManager function before Alpine initializes
function taskManager() {
    return {
        view: '{{ $view }}',
        currentTask: null,
        draggedTask: null,
        modalOpen: false,
        tasksData: [],
        
        openCreateModal() {
            // Dispatch event to modal
            window.dispatchEvent(new CustomEvent('open-task-modal', { detail: { task: null } }));
        },
        
        openEditModal(task) {
            // Find full task data from tasksData
            const fullTask = this.tasksData.find(t => t.id === task.id) || task;
            // Dispatch event to modal
            window.dispatchEvent(new CustomEvent('open-task-modal', { detail: { task: fullTask } }));
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
</script>
@endpush

@section('content')
<div class="space-y-6" x-data="taskManager()" x-init="tasksData = @js($tasksJson ?? [])">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $type === 'personal' ? 'My Tasks' : 'Group Tasks' }}</h1>
                <p class="text-sm text-gray-600 mt-1">Manage your {{ $type === 'personal' ? 'personal' : 'group' }} tasks</p>
            </div>
            <div class="flex items-center space-x-3">
                <!-- View Toggle -->
                <div class="flex items-center bg-gray-100 rounded-lg p-1">
                    <button @click="view = 'board'" 
                            :class="view === 'board' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                        </svg>
                        Board
                    </button>
                    <button @click="view = 'list'" 
                            :class="view === 'list' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        List
                    </button>
                </div>
                <!-- Add Task Button -->
                <button @click="openCreateModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Task
                </button>
            </div>
        </div>
    </div>

    <!-- Board View -->
    <div x-show="view === 'board'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Todo Column -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-700">To Do</h2>
                <span class="bg-gray-200 text-gray-700 text-xs font-semibold px-2 py-1 rounded-full">{{ $tasksByStatus['todo']->count() }}</span>
            </div>
            <div class="space-y-3 min-h-[400px] transition-colors duration-200" 
                 data-status="todo"
                 @drop="handleDrop($event, 'todo')" 
                 @dragover.prevent
                 @dragenter.prevent
                 :class="draggedTask ? 'bg-blue-50 border-2 border-blue-300 border-dashed rounded-lg p-2' : ''">
                @foreach($tasksByStatus['todo'] as $task)
                    @include('admin.tasks.partials.task-card', ['task' => $task])
                @endforeach
            </div>
        </div>

        <!-- In Progress Column -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-700">In Progress</h2>
                <span class="bg-blue-200 text-blue-700 text-xs font-semibold px-2 py-1 rounded-full">{{ $tasksByStatus['in_progress']->count() }}</span>
            </div>
            <div class="space-y-3 min-h-[400px] transition-colors duration-200" 
                 data-status="in_progress"
                 @drop="handleDrop($event, 'in_progress')" 
                 @dragover.prevent
                 @dragenter.prevent
                 :class="draggedTask ? 'bg-blue-50 border-2 border-blue-300 border-dashed rounded-lg p-2' : ''">
                @foreach($tasksByStatus['in_progress'] as $task)
                    @include('admin.tasks.partials.task-card', ['task' => $task])
                @endforeach
            </div>
        </div>

        <!-- Done Column -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-700">Done</h2>
                <span class="bg-green-200 text-green-700 text-xs font-semibold px-2 py-1 rounded-full">{{ $tasksByStatus['done']->count() }}</span>
            </div>
            <div class="space-y-3 min-h-[400px] transition-colors duration-200" 
                 data-status="done"
                 @drop="handleDrop($event, 'done')" 
                 @dragover.prevent
                 @dragenter.prevent
                 :class="draggedTask ? 'bg-blue-50 border-2 border-blue-300 border-dashed rounded-lg p-2' : ''">
                @foreach($tasksByStatus['done'] as $task)
                    @include('admin.tasks.partials.task-card', ['task' => $task])
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


@push('scripts')
<script>
function taskManager() {
    return {
        view: '{{ $view }}',
        currentTask: null,
        draggedTask: null,
        modalOpen: false,
        tasksData: [],
        
        openCreateModal() {
            // Dispatch event to modal
            window.dispatchEvent(new CustomEvent('open-task-modal', { detail: { task: null } }));
        },
        
        openEditModal(task) {
            // Find full task data from tasksData
            const fullTask = this.tasksData.find(t => t.id === task.id) || task;
            // Dispatch event to modal
            window.dispatchEvent(new CustomEvent('open-task-modal', { detail: { task: fullTask } }));
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
</script>
@endpush
@endsection
