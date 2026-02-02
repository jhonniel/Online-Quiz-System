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
            selectedTaskListId: '{{ $taskListId ?? '' }}',
            currentTask: null,
            draggedTask: null,
            modalOpen: false,
            tasksData: [],
            searchQuery: '',
            customBoardModalOpen: false,
            taskListModalOpen: false,
            customPriorityModalOpen: false,
            editingTaskList: null,
            editingCustomPriority: null,
            taskListForm: {
                name: '',
                description: '',
                color: 'blue'
            },
            customPriorityForm: {
                name: '',
                color: 'bg-gray-100 text-gray-800'
            },
            editingCustomBoard: null,
            customBoardForm: {
                name: '',
                color: 'blue',
                type: '{{ $type }}',
                task_list_id: '{{ $taskListId ?? '' }}'
            },
            draggedBoard: null,
            
            openCreateModal() {
                // Simple approach: directly access the modal component
                const modal = document.getElementById('task-modal');
                if (modal && window.Alpine) {
                    // Wait for Alpine to be ready
                    setTimeout(() => {
                        try {
                            const modalData = Alpine.$data(modal);
                            if (modalData && modalData.openModal) {
                                modalData.openModal(null);
                        } else {
                                // Fallback: show modal and reset form
                                modal.classList.remove('hidden');
                                modal.style.display = 'block';
                                const form = modal.querySelector('form');
                                if (form) form.reset();
                                // Reset Alpine data manually
                                if (modalData) {
                                    modalData.currentTask = null;
                                    modalData.formData = {
                                        title: '',
                                        description: '',
                                        notes: '',
                                        status: 'todo',
                                        task_list_id: '{{ $taskListId ?? '' }}',
                                        due_date: '',
                                        due_time: '',
                                        assigned_users: [],
                                        type: '{{ $type }}',
                                        parent_id: null
                                    };
                                }
                        }
                    } catch (e) {
                            console.error('Error opening modal:', e);
                            // Fallback: just show the modal
                            modal.classList.remove('hidden');
                            modal.style.display = 'block';
                    }
                    }, 100);
                } else {
                    alert('Modal not found. Please refresh the page.');
                }
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
                
                // Allow dropping tasks on locked boards (lock only prevents board reordering)
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
            },
            
            openCustomBoardModal() {
                this.customBoardModalOpen = true;
                this.editingCustomBoard = null;
                this.customBoardForm = {
                    name: '',
                    color: 'blue',
                    type: '{{ $type }}',
                    task_list_id: '{{ $taskListId ?? '' }}'
                };
            },
            
            closeCustomBoardModal() {
                this.customBoardModalOpen = false;
                this.editingCustomBoard = null;
                this.customBoardForm = {
                    name: '',
                    color: 'blue',
                    type: '{{ $type }}',
                    task_list_id: '{{ $taskListId ?? '' }}'
                };
            },
            
            editCustomBoard(boardId) {
                const boards = @json($customBoards ?? []);
                const board = boards.find(b => b.id === boardId);
                if (board) {
                    this.editingCustomBoard = board;
                    this.customBoardForm = {
                        name: board.name,
                        color: board.color,
                        type: board.type,
                        task_list_id: board.task_list_id || ''
                    };
                    this.customBoardModalOpen = true;
                }
            },
            
            async saveCustomBoard() {
                if (!this.customBoardForm.name || !this.customBoardForm.name.trim()) {
                    alert('Please enter a board name');
                    return;
                }
                
                const url = this.editingCustomBoard 
                    ? `/admin/tasks/custom-boards/${this.editingCustomBoard.id}`
                    : '/admin/tasks/custom-boards';
                const method = this.editingCustomBoard ? 'PUT' : 'POST';
                
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.customBoardForm)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.closeCustomBoardModal();
                        location.reload();
                    } else {
                        let errorMessage = data.message || 'Failed to save custom board';
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat().join('\n');
                            errorMessage = errorMessage + '\n\n' + errorList;
                        }
                        alert('Error: ' + errorMessage);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while saving the custom board. Please try again.');
                }
            },
            
            async deleteCustomBoard(boardId) {
                if (!confirm('Are you sure you want to delete this custom board? All tasks in this board will be moved to "To Do".')) {
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/tasks/custom-boards/${boardId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete custom board'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the custom board');
                }
            },
            
            handleBoardDragStart(event, boardId) {
                // Check if board is locked
                const boardElement = event.currentTarget;
                const isLocked = boardElement.dataset.isLocked === 'true';
                
                if (isLocked) {
                    event.preventDefault();
                    return false;
                }
                
                this.draggedBoard = boardId;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', boardId);
                event.currentTarget.style.opacity = '0.5';
            },
            
            handleBoardDragEnd(event) {
                this.draggedBoard = null;
                document.querySelectorAll('[data-board-id]').forEach(el => {
                    el.style.opacity = '1';
                });
            },
            
            handleBoardDrop(event, targetBoardId) {
                event.preventDefault();
                event.stopPropagation();
                
                if (!this.draggedBoard || this.draggedBoard === targetBoardId) {
                    this.draggedBoard = null;
                    document.querySelectorAll('[data-board-id]').forEach(el => {
                        el.style.opacity = '1';
                    });
                    return;
                }
                
                // Check if target board is locked
                const targetBoardElement = document.querySelector(`[data-board-id="${targetBoardId}"]`);
                if (targetBoardElement && targetBoardElement.dataset.isLocked === 'true') {
                    this.draggedBoard = null;
                    document.querySelectorAll('[data-board-id]').forEach(el => {
                        el.style.opacity = '1';
                    });
                    alert('Cannot move board to a locked position');
                    return;
                }
                
                // Check if dragged board is locked
                const draggedBoardElement = document.querySelector(`[data-board-id="${this.draggedBoard}"]`);
                if (draggedBoardElement && draggedBoardElement.dataset.isLocked === 'true') {
                    this.draggedBoard = null;
                    document.querySelectorAll('[data-board-id]').forEach(el => {
                        el.style.opacity = '1';
                    });
                    return;
                }
                
                // Get all boards and their current order
                const boards = @json($customBoards ?? []);
                const boardElements = Array.from(document.querySelectorAll('[data-board-id]'));
                
                // Find the dragged board and target board
                const draggedBoardEl = boardElements.find(el => parseInt(el.dataset.boardId) === this.draggedBoard);
                const targetBoardEl = boardElements.find(el => parseInt(el.dataset.boardId) === targetBoardId);
                
                if (!draggedBoardEl || !targetBoardEl) {
                    this.draggedBoard = null;
                    return;
                }
                
                // Get the container
                const container = draggedBoardEl.parentElement;
                if (!container) {
                    this.draggedBoard = null;
                    return;
                }
                
                // Get all board elements in current order
                const allBoards = Array.from(container.querySelectorAll('[data-board-id]'));
                const draggedIndex = allBoards.indexOf(draggedBoardEl);
                const targetIndex = allBoards.indexOf(targetBoardEl);
                
                // Reorder in DOM
                if (draggedIndex < targetIndex) {
                    container.insertBefore(draggedBoardEl, targetBoardEl.nextSibling);
                } else {
                    container.insertBefore(draggedBoardEl, targetBoardEl);
                }
                
                // Calculate new order based on DOM position
                const reorderedBoards = Array.from(container.querySelectorAll('[data-board-id]'));
                const newOrder = reorderedBoards.map((el, index) => {
                    const boardId = parseInt(el.dataset.boardId);
                    return {
                        id: boardId,
                        order: index + 1
                    };
                });
                
                // Update order on server
                fetch('/admin/tasks/custom-boards/update-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ boards: newOrder })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error updating board order: ' + (data.message || 'Unknown error'));
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating board order');
                    location.reload();
                });
                
                this.draggedBoard = null;
            },
            
            async toggleBoardLock(boardId) {
                try {
                    const response = await fetch(`/admin/tasks/custom-boards/${boardId}/toggle-lock`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to toggle lock status'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while toggling lock status');
                }
            },
            
            async toggleSubtaskStatus(subtaskId, isDone) {
                try {
                    const response = await fetch(`/admin/tasks/${subtaskId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            status: isDone ? 'done' : 'todo'
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Reload to update the UI
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to update subtask'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while updating the subtask');
                }
            },
            
            async changePriority(taskId, priority) {
                try {
                    const response = await fetch(`/admin/tasks/${taskId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            priority: priority
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Reload to update the UI
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to update priority'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while updating the priority');
                }
            },
            
            changeTaskList() {
                const url = new URL(window.location.href);
                if (this.selectedTaskListId) {
                    url.searchParams.set('list_id', this.selectedTaskListId);
                } else {
                    url.searchParams.delete('list_id');
                }
                window.location.href = url.toString();
            },
            
            openTaskListModal() {
                this.editingTaskList = null;
                this.taskListForm = {
                    name: '',
                    description: '',
                    color: 'blue'
                };
                this.taskListModalOpen = true;
            },
            
            closeTaskListModal() {
                this.taskListModalOpen = false;
                this.editingTaskList = null;
                this.taskListForm = {
                    name: '',
                    description: '',
                    color: 'blue'
                };
            },
            
            async saveTaskList() {
                const url = this.editingTaskList 
                    ? `/admin/tasks/task-lists/${this.editingTaskList}`
                    : '/admin/tasks/task-lists';
                const method = this.editingTaskList ? 'PUT' : 'POST';
                
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            name: this.taskListForm.name,
                            description: this.taskListForm.description,
                            color: this.taskListForm.color
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to save task list'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while saving the task list');
                }
            },
            
            editTaskList(listId) {
                const taskLists = @json($taskLists ?? []);
                const taskList = taskLists.find(l => l.id == listId);
                if (taskList) {
                    this.editingTaskList = taskList.id;
                    this.taskListForm = {
                        name: taskList.name,
                        description: taskList.description || '',
                        color: taskList.color || 'blue'
                    };
                    this.taskListModalOpen = true;
                }
            },
            
            async deleteTaskList(listId) {
                if (!confirm('Are you sure you want to delete this task list? Tasks in this list will be moved to "No List".')) {
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/tasks/task-lists/${listId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Redirect to tasks without list_id
                        const url = new URL(window.location.href);
                        url.searchParams.delete('list_id');
                        window.location.href = url.toString();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete task list'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the task list');
                }
            },
            
            openCustomPriorityModal() {
                this.customPriorityModalOpen = true;
                this.editingCustomPriority = null;
                this.customPriorityForm = {
                    name: '',
                    color: 'bg-gray-100 text-gray-800'
                };
            },
            
            closeCustomPriorityModal() {
                this.customPriorityModalOpen = false;
                this.editingCustomPriority = null;
                this.customPriorityForm = {
                    name: '',
                    color: 'bg-gray-100 text-gray-800'
                };
            },
            
            editCustomPriority(priorityId) {
                const priorities = @json($customPriorities ?? []);
                const priority = priorities.find(p => p.id === priorityId);
                if (priority) {
                    this.editingCustomPriority = priority;
                    this.customPriorityForm = {
                        name: priority.name,
                        color: priority.color
                    };
                    this.customPriorityModalOpen = true;
                }
            },
            
            async saveCustomPriority() {
                if (!this.customPriorityForm.name || !this.customPriorityForm.name.trim()) {
                    alert('Please enter a priority name');
                    return;
                }
                
                const url = this.editingCustomPriority 
                    ? `/admin/tasks/custom-priorities/${this.editingCustomPriority.id}`
                    : '/admin/tasks/custom-priorities';
                const method = this.editingCustomPriority ? 'PUT' : 'POST';
                
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.customPriorityForm)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.closeCustomPriorityModal();
                        location.reload();
                    } else {
                        let errorMessage = data.message || 'Failed to save custom priority';
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat().join('\n');
                            errorMessage = errorMessage + '\n\n' + errorList;
                        }
                        alert('Error: ' + errorMessage);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while saving the custom priority. Please try again.');
                }
            },
            
            async deleteCustomPriority(priorityId) {
                if (!confirm('Are you sure you want to delete this custom priority? All tasks using this priority will be set to "medium".')) {
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/tasks/custom-priorities/${priorityId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete custom priority'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the custom priority');
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
                notes: '',
                status: 'todo',
                priority: 'medium',
                task_list_id: '{{ $taskListId ?? '' }}',
                due_date: '',
                due_time: '',
                assigned_users: [],
                invite_users: [],
                invite_role: 'viewer',
                share_users: [],
                share_role: 'viewer',
                type: '{{ $type }}',
                parent_id: null
            },
            inviteLink: '',
            shareLink: '',
            joinCode: '',
            shareMethod: 'code',
            newComment: '',
            selectedFile: null,
            currentUserId: {{ auth()->id() }},
            subtaskModalOpen: false,
            subtaskForm: {
                title: '',
                description: '',
                due_date: '',
                due_time: ''
            },
            
            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                return date.toLocaleDateString('en-US', options);
            },
            
            async joinByCode() {
                if (!this.joinCode || this.joinCode.trim().length !== 8) {
                    alert('Please enter a valid 8-character invite code');
                    return;
                }
                
                try {
                    const response = await fetch('/admin/tasks/join-by-code', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            invite_code: this.joinCode.toUpperCase().trim()
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Successfully joined the task!');
                        this.joinCode = '';
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to join task'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while joining the task. Please try again.');
                }
            },
            
            openModal(task) {
                const taskType = '{{ $type }}'; // 'personal' or 'group'
                
                if (task) {
                    // Editing existing task
                    this.currentTask = {
                        id: task.id,
                        title: task.title || '',
                        description: task.description || '',
                        notes: task.notes || '',
                        due_date: task.due_date || null,
                        type: task.type || taskType,
                        attachments: Array.isArray(task.attachments) ? task.attachments : [],
                        comments: Array.isArray(task.comments) ? task.comments : [],
                        assignments: Array.isArray(task.assignments) ? task.assignments : [],
                        subtasks: Array.isArray(task.subtasks) ? task.subtasks : []
                    };
                    this.formData = {
                        title: task.title || '',
                        description: task.description || '',
                        notes: task.notes || '',
                        status: task.status || 'todo',
                        priority: task.priority || 'medium',
                        task_list_id: task.task_list_id || '{{ $taskListId ?? '' }}',
                        due_date: task.due_date ? new Date(task.due_date).toISOString().split('T')[0] : '',
                        due_time: task.due_date ? new Date(task.due_date).toTimeString().slice(0, 5) : '',
                        assigned_users: task.assignments ? task.assignments.map(a => a.user_id) : [],
                        invite_users: [],
                        share_users: [],
                        share_role: 'viewer',
                        type: task.type || taskType,
                        parent_id: task.parent_id || null
                    };
                    this.shareMethod = 'code';
                    this.inviteLink = '';
                    this.shareLink = '';
                    
                    // Load task invitation info if available
                    if (task.invite_code) {
                        this.currentTask.invite_code = task.invite_code;
                    }
                    if (task.is_owner !== undefined) {
                        this.currentTask.is_owner = task.is_owner;
                    }
                    if (task.is_member !== undefined) {
                        this.currentTask.is_member = task.is_member;
                    }
                } else {
                    // Creating new task
                    this.currentTask = null;
                    this.formData = {
                        title: '',
                        description: '',
                        notes: '',
                        status: 'todo',
                        task_list_id: '{{ $taskListId ?? '' }}',
                        due_date: '',
                        due_time: '',
                        assigned_users: [],
                        invite_users: [],
                        share_users: [],
                        share_role: 'viewer',
                        type: taskType || '{{ $type }}',
                        parent_id: null
                    };
                    this.shareMethod = 'code';
                    this.inviteLink = '';
                    this.shareLink = '';
                    this.newComment = '';
                    this.selectedFile = null;
                }
                
                // Show modal
                const modal = document.getElementById('task-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'block';
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
                    notes: '',
                    status: 'todo',
                    priority: 'medium',
                    task_list_id: '{{ $taskListId ?? '' }}',
                    due_date: '',
                    due_time: '',
                    assigned_users: [],
                    invite_users: [],
                    share_users: [],
                    share_role: 'viewer',
                    type: taskType,
                    parent_id: null
                };
                this.shareMethod = 'code';
                this.inviteLink = '';
                this.shareLink = '';
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
                    notes: this.formData.notes || '',
                    type: this.formData.type,
                    status: (this.formData.status && this.formData.status !== '') ? this.formData.status : 'todo',
                    task_list_id: this.formData.task_list_id && this.formData.task_list_id !== '' ? this.formData.task_list_id : null,
                    parent_id: this.formData.parent_id || null,
                };
                
                if (this.formData.due_date) {
                    requestData.due_date = this.formData.due_date;
                    if (this.formData.due_time) {
                        requestData.due_time = this.formData.due_time;
                    }
                }
                
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
                    
                    if (data.success) {
                        const taskId = data.task.id || data.task.id;
                        
                        // Upload file if selected
                        if (this.selectedFile) {
                            await this.uploadFile(taskId);
                        }
                        
                        // Assign users if group task
                        if (this.formData.type === 'group' && this.formData.assigned_users.length > 0) {
                            if (!this.currentTask) {
                                await this.assignUsers(taskId);
                            }
                        }
                        
                        // Close modal and reload
                        this.closeModal();
                        location.reload();
                    } else {
                        let errorMessage = data.message || 'Failed to save task';
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat().join('\n');
                            errorMessage = errorMessage + '\n\n' + errorList;
                        }
                        alert('Error: ' + errorMessage);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while saving the task. Please try again.');
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
            
            openSubtaskModal() {
                this.subtaskModalOpen = true;
                this.subtaskForm = {
                    title: '',
                    description: '',
                    due_date: '',
                    due_time: ''
                };
            },
            
            closeSubtaskModal() {
                this.subtaskModalOpen = false;
                this.subtaskForm = {
                    title: '',
                    description: '',
                    due_date: '',
                    due_time: ''
                };
            },
            
            async saveSubtask() {
                if (!this.subtaskForm.title || !this.subtaskForm.title.trim()) {
                    alert('Please enter a subtask title');
                    return;
                }
                
                if (!this.currentTask || !this.currentTask.id) {
                    alert('No parent task selected');
                    return;
                }
                
                try {
                    const response = await fetch('/admin/tasks', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            title: this.subtaskForm.title.trim(),
                            description: this.subtaskForm.description || '',
                            type: 'personal',
                            parent_id: this.currentTask.id,
                            due_date: this.subtaskForm.due_date || null,
                            due_time: this.subtaskForm.due_time || null
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.closeSubtaskModal();
                        location.reload();
                    } else {
                        let errorMessage = data.message || 'Failed to create subtask';
                        if (data.errors) {
                            const errorList = Object.values(data.errors).flat().join('\n');
                            errorMessage = errorMessage + '\n\n' + errorList;
                        }
                        alert('Error: ' + errorMessage);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while creating the subtask');
                }
            },
            
            async toggleSubtaskStatus(subtaskId, isDone) {
                try {
                    const response = await fetch(`/admin/tasks/${subtaskId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            status: isDone ? 'done' : 'todo'
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const subtask = this.currentTask.subtasks.find(s => s.id === subtaskId);
                        if (subtask) {
                            subtask.status = isDone ? 'done' : 'todo';
                        }
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to update subtask'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while updating the subtask');
                }
            },
            
            async deleteSubtask(subtaskId) {
                if (!confirm('Are you sure you want to delete this subtask?')) {
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/tasks/${subtaskId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.currentTask.subtasks = this.currentTask.subtasks.filter(s => s.id !== subtaskId);
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete subtask'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the subtask');
                }
            },
            
            async addComment() {
                if (!this.newComment.trim() || !this.currentTask) return;
                
                const commentText = this.newComment.trim();
                this.newComment = ''; // Clear input immediately for better UX
                
                try {
                    const response = await fetch(`/admin/tasks/${this.currentTask.id}/comments`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            comment: commentText
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        if (!this.currentTask.comments) {
                            this.currentTask.comments = [];
                        }
                        this.currentTask.comments.push(data.comment);
                        
                        // Scroll to bottom of comments container
                        this.$nextTick(() => {
                            const commentsContainer = document.getElementById('comments-container');
                            if (commentsContainer) {
                                commentsContainer.scrollTop = commentsContainer.scrollHeight;
                            }
                        });
                    } else {
                        // Restore comment if failed
                        this.newComment = commentText;
                        alert('Failed to add comment: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error adding comment:', error);
                    // Restore comment if failed
                    this.newComment = commentText;
                    alert('An error occurred while adding the comment. Please try again.');
                }
            },
            
            formatCommentTime(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                const now = new Date();
                const diffMs = now - date;
                const diffMins = Math.floor(diffMs / 60000);
                const diffHours = Math.floor(diffMs / 3600000);
                const diffDays = Math.floor(diffMs / 86400000);
                
                if (diffMins < 1) return 'Just now';
                if (diffMins < 60) return `${diffMins}m ago`;
                if (diffHours < 24) return `${diffHours}h ago`;
                if (diffDays < 7) return `${diffDays}d ago`;
                
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined });
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
            
            isTaskOwner() {
                if (!this.currentTask || !this.currentTask.id) return false;
                return this.currentTask.is_owner || this.currentTask.created_by === this.currentUserId;
            },
            
            isTaskMember() {
                if (!this.currentTask || !this.currentTask.id) return false;
                return this.currentTask.is_member || this.currentTask.is_owner;
            },
            
            async convertToGroup() {
                if (!this.currentTask || !this.currentTask.id) {
                    alert('No task selected');
                    return;
                }
                
                if (this.currentTask.type !== 'personal') {
                    alert('This task is already a group task');
                    return;
                }
                
                if (this.shareMethod === 'manual' && (!this.formData.share_users || this.formData.share_users.length === 0)) {
                    alert('Please select at least one user to invite');
                    return;
                }
                
                try {
                    const requestData = {
                        share_method: this.shareMethod,
                    };
                    
                    if (this.shareMethod === 'manual') {
                        requestData.user_ids = this.formData.share_users;
                        requestData.role = this.formData.share_role;
                    }
                    
                    const response = await fetch(`/admin/tasks/${this.currentTask.id}/convert-to-group`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(requestData)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        let message = data.message;
                        
                        if (data.invite_code) {
                            message += '\n\nInvite Code: ' + data.invite_code;
                        }
                        
                        if (data.invite_link) {
                            message += '\n\nInvite Link: ' + data.invite_link;
                        }
                        
                        if (data.invited_count !== undefined) {
                            message += '\n\n' + data.invited_count + ' invitation(s) sent.';
                        }
                        
                        alert(message);
                        
                        // Close modal and redirect to group tasks
                        this.closeModal();
                        window.location.href = '/admin/tasks?type=group';
                    } else {
                        alert('Error: ' + (data.message || 'Failed to convert task'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while converting the task. Please try again.');
                }
            },
            
            copyInviteCode() {
                const input = document.getElementById('invite-code-input');
                if (input && this.currentTask && this.currentTask.invite_code) {
                    input.select();
                    document.execCommand('copy');
                    alert('✅ Invite code copied to clipboard!\n\nYou can now paste it and share with others via:\n• Email\n• Chat apps (WhatsApp, Telegram, etc.)\n• Messaging platforms\n• Social media\n\nUsers must be logged in to join using this code.');
                } else if (!this.currentTask || !this.currentTask.invite_code) {
                    alert('Please generate an invite code first.');
                }
            },
            
            async generateInviteCode() {
                if (!this.currentTask || !this.currentTask.id) return;
                
                try {
                    const response = await fetch(`/admin/tasks/${this.currentTask.id}/generate-invite-code`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.currentTask.invite_code = data.invite_code;
                        alert('Invite code generated successfully!');
                    } else {
                        alert('Error: ' + (data.message || 'Failed to generate invite code'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while generating invite code');
                }
            },
            
            async getInviteLink() {
                if (!this.currentTask || !this.currentTask.id) return;
                
                try {
                    const response = await fetch(`/admin/tasks/${this.currentTask.id}/get-invite-link`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.inviteLink = data.invite_link;
                        const input = document.getElementById('invite-link-input');
                        if (input) {
                            input.value = data.invite_link;
                        }
                    } else {
                        alert('Error: ' + (data.message || 'Failed to get invite link'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while getting invite link');
                }
            },
            
            copyInviteLink() {
                const input = document.getElementById('invite-link-input');
                if (input && this.inviteLink) {
                    input.select();
                    document.execCommand('copy');
                    alert('✅ Invite link copied to clipboard!\n\nYou can now paste it and share with others via:\n• Email\n• Chat apps (WhatsApp, Telegram, etc.)\n• Messaging platforms\n• Social media\n\nUsers can click the link directly to join (must be logged in).');
                } else if (!this.inviteLink) {
                    alert('Please generate an invite link first.');
                }
            },
            
            async sendInvitations() {
                if (!this.currentTask || !this.currentTask.id) return;
                
                if (!this.formData.invite_users || this.formData.invite_users.length === 0) {
                    alert('Please select at least one user to invite');
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/tasks/${this.currentTask.id}/invite-users`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            user_ids: this.formData.invite_users,
                            role: this.formData.invite_role
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert(data.message);
                        this.formData.invite_users = [];
                        // Optionally reload to show updated invitations
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to send invitations'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while sending invitations');
                }
            },
            
            async generateShareLink() {
                if (!this.currentTask || !this.currentTask.id) return;
                
                if (this.currentTask.type !== 'personal') {
                    alert('Share link is only available for personal tasks');
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/tasks/${this.currentTask.id}/generate-share-link`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.shareLink = data.share_link;
                        const input = document.getElementById('share-link-input');
                        if (input) {
                            input.value = data.share_link;
                        }
                        alert('Share link generated! Task will be converted to group when someone joins via this link.');
                    } else {
                        alert('Error: ' + (data.message || 'Failed to generate share link'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while generating share link');
                }
            },
            
            copyShareLink() {
                const input = document.getElementById('share-link-input');
                if (input && this.shareLink) {
                    input.select();
                    document.execCommand('copy');
                    alert('Share link copied to clipboard! You can now paste it anywhere to share.');
                } else if (!this.shareLink) {
                    alert('Please generate a share link first.');
                }
            },
            
            shareViaEmail() {
                if (!this.currentTask || !this.currentTask.id) return;
                
                const code = this.currentTask.invite_code;
                const link = this.inviteLink;
                
                if (!code && !link) {
                    alert('Please generate an invite code or link first.');
                    return;
                }
                
                let shareText = `Join my task: ${this.currentTask.title}\n\n`;
                if (code) {
                    shareText += `Invite Code: ${code}\n`;
                }
                if (link) {
                    shareText += `Invite Link: ${link}\n`;
                }
                shareText += `\nNote: You must be logged in to join this task.`;
                
                const subject = encodeURIComponent(`Invitation to join: ${this.currentTask.title}`);
                const body = encodeURIComponent(shareText);
                window.location.href = `mailto:?subject=${subject}&body=${body}`;
            },
            
            shareViaClipboard() {
                if (!this.currentTask || !this.currentTask.id) return;
                
                const code = this.currentTask.invite_code;
                const link = this.inviteLink;
                
                if (!code && !link) {
                    alert('Please generate an invite code or link first.');
                    return;
                }
                
                let shareText = `Join my task: ${this.currentTask.title}\n\n`;
                if (code) {
                    shareText += `Invite Code: ${code}\n`;
                }
                if (link) {
                    shareText += `Invite Link: ${link}\n`;
                }
                shareText += `\nNote: You must be logged in to join this task.`;
                
                // Copy to clipboard
                navigator.clipboard.writeText(shareText).then(() => {
                    alert('Invitation details copied to clipboard! You can now paste it anywhere.');
                }).catch(() => {
                    // Fallback for older browsers
                    const textarea = document.createElement('textarea');
                    textarea.value = shareText;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    alert('Invitation details copied to clipboard!');
                });
            },
            
            sharePersonalTaskViaEmail() {
                if (!this.shareLink) {
                    alert('Please generate a share link first.');
                    return;
                }
                
                const shareText = `Join my task: ${this.currentTask ? this.currentTask.title : 'Task'}\n\nShare Link: ${this.shareLink}\n\nNote: You must be logged in to join this task. The task will be converted to a group task when you join.`;
                const subject = encodeURIComponent(`Invitation to join: ${this.currentTask ? this.currentTask.title : 'Task'}`);
                const body = encodeURIComponent(shareText);
                window.location.href = `mailto:?subject=${subject}&body=${body}`;
            },
            
            // Share functions for task cards/rows
            async shareTaskCode(taskId, inviteCode) {
                if (!inviteCode) {
                    // Generate invite code first
                    try {
                        const response = await fetch(`/admin/tasks/${taskId}/generate-invite-code`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            inviteCode = data.invite_code;
                            // Update task in tasksData
                            const task = this.tasksData.find(t => t.id === taskId);
                            if (task) {
                                task.invite_code = inviteCode;
                            }
                        } else {
                            alert('Error: ' + (data.message || 'Failed to generate invite code'));
                            return;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while generating invite code');
                        return;
                    }
                }
                
                // Copy to clipboard
                navigator.clipboard.writeText(inviteCode).then(() => {
                    alert('✅ Invite code copied to clipboard!\n\nCode: ' + inviteCode + '\n\nYou can now paste it and share with others.');
                }).catch(() => {
                    // Fallback
                    const textarea = document.createElement('textarea');
                    textarea.value = inviteCode;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    alert('✅ Invite code copied to clipboard!\n\nCode: ' + inviteCode);
                });
            },
            
            async shareTaskLink(taskId) {
                try {
                    const response = await fetch(`/admin/tasks/${taskId}/get-invite-link`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        const link = data.invite_link;
                        // Copy to clipboard
                        navigator.clipboard.writeText(link).then(() => {
                            alert('✅ Invite link copied to clipboard!\n\nLink: ' + link + '\n\nYou can now paste it and share with others.');
                        }).catch(() => {
                            // Fallback
                            const textarea = document.createElement('textarea');
                            textarea.value = link;
                            document.body.appendChild(textarea);
                            textarea.select();
                            document.execCommand('copy');
                            document.body.removeChild(textarea);
                            alert('✅ Invite link copied to clipboard!\n\nLink: ' + link);
                        });
                    } else {
                        alert('Error: ' + (data.message || 'Failed to get invite link'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while getting invite link');
                }
            },
            
            async shareTaskViaEmail(taskId, inviteCode) {
                const task = this.tasksData.find(t => t.id === taskId);
                if (!task) {
                    alert('Task not found');
                    return;
                }
                
                let code = inviteCode;
                if (!code) {
                    // Generate invite code first
                    try {
                        const response = await fetch(`/admin/tasks/${taskId}/generate-invite-code`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            code = data.invite_code;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                }
                
                let shareText = `Join my task: ${task.title}\n\n`;
                if (code) {
                    shareText += `Invite Code: ${code}\n`;
                }
                shareText += `\nNote: You must be logged in to join this task.`;
                
                const subject = encodeURIComponent(`Invitation to join: ${task.title}`);
                const body = encodeURIComponent(shareText);
                window.location.href = `mailto:?subject=${subject}&body=${body}`;
            },
            
            async sharePersonalTaskLink(taskId) {
                try {
                    const response = await fetch(`/admin/tasks/${taskId}/generate-share-link`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        const link = data.share_link;
                        // Copy to clipboard
                        navigator.clipboard.writeText(link).then(() => {
                            alert('✅ Share link generated and copied to clipboard!\n\nLink: ' + link + '\n\nTask will be converted to group when someone joins via this link.');
                        }).catch(() => {
                            // Fallback
                            const textarea = document.createElement('textarea');
                            textarea.value = link;
                            document.body.appendChild(textarea);
                            textarea.select();
                            document.execCommand('copy');
                            document.body.removeChild(textarea);
                            alert('✅ Share link generated and copied to clipboard!\n\nLink: ' + link);
                        });
                    } else {
                        alert('Error: ' + (data.message || 'Failed to generate share link'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while generating share link');
                }
            },
            
            async sharePersonalTaskViaEmail(taskId) {
                const task = this.tasksData.find(t => t.id === taskId);
                if (!task) {
                    alert('Task not found');
                    return;
                }
                
                // Generate share link first
                try {
                    const response = await fetch(`/admin/tasks/${taskId}/generate-share-link`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        const link = data.share_link;
                        const shareText = `Join my task: ${task.title}\n\nShare Link: ${link}\n\nNote: You must be logged in to join this task. The task will be converted to a group task when you join.`;
                        const subject = encodeURIComponent(`Invitation to join: ${task.title}`);
                        const body = encodeURIComponent(shareText);
                        window.location.href = `mailto:?subject=${subject}&body=${body}`;
                    } else {
                        alert('Error: ' + (data.message || 'Failed to generate share link'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while generating share link');
                }
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
        // Listen for custom event to open modal
            window.addEventListener('open-task-modal', function(e) {
                const modal = document.getElementById('task-modal');
            if (modal && window.Alpine) {
                setTimeout(() => {
                        try {
                            const modalComponent = Alpine.$data(modal);
                            if (modalComponent && modalComponent.openModal) {
                                modalComponent.openModal(e.detail.task);
                            }
                        } catch (error) {
                        console.error('Error opening modal:', error);
                    }
                }, 100);
            }
        });
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

        <!-- Task List Selector (for personal tasks only) -->
        @if($type === 'personal')
        <div class="px-6 py-3 bg-blue-50 border-b border-blue-200 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center space-x-3 flex-1">
                <label class="text-sm font-medium text-gray-700">Task List:</label>
                <select x-model="selectedTaskListId" 
                        @change="changeTaskList()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 min-w-[200px]">
                    <option value="">-- Select List --</option>
                    @foreach($taskLists ?? [] as $list)
                        <option value="{{ $list->id }}" {{ ($taskListId ?? null) == $list->id ? 'selected' : '' }}>
                            {{ $list->name }}
                        </option>
                    @endforeach
                </select>
                <button @click="openTaskListModal()" 
                        type="button"
                        class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New List
                </button>
                @if($taskListId && isset($taskLists))
                    @php
                        $currentList = $taskLists->firstWhere('id', $taskListId);
                    @endphp
                    @if($currentList)
                        <div class="relative" x-data="{ open: false }">
                            <button @click.stop="open = !open" 
                                    type="button"
                                    class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                                Share List
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-cloak
                                 class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg border border-gray-200 py-2 z-50">
                                <div class="px-3 py-2 border-b border-gray-100">
                                    <p class="text-xs font-medium text-gray-700 mb-2">Share Task List</p>
                                    <p class="text-xs text-gray-500 mb-3">Share this entire task list with others. They will see all tasks in this list.</p>
                                    <button @click.stop="shareTaskListCode({{ $currentList->id }}, '{{ $currentList->invite_code ?? '' }}'); open = false" 
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 text-gray-700 flex items-center space-x-2 mb-2">
                                        <span>🔑</span>
                                        <span>Copy Invite Code</span>
                                    </button>
                                    <button @click.stop="shareTaskListLink({{ $currentList->id }}); open = false" 
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 text-gray-700 flex items-center space-x-2 mb-2">
                                        <span>🔗</span>
                                        <span>Copy Share Link</span>
                                    </button>
                                    <button @click.stop="shareTaskListViaEmail({{ $currentList->id }}, '{{ $currentList->invite_code ?? '' }}'); open = false" 
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 text-gray-700 flex items-center space-x-2">
                                        <span>📧</span>
                                        <span>Share via Email</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button @click="editTaskList({{ $currentList->id }})" 
                                type="button"
                                class="px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </button>
                        <button @click="deleteTaskList({{ $currentList->id }})" 
                                type="button"
                                class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    @endif
                @endif
            </div>
        </div>
        @endif

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

    <!-- Join Task Section (for group tasks when empty) -->
    @if($type === 'group' && count($tasks) === 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <div class="max-w-md mx-auto">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Group Tasks Yet</h3>
            <p class="text-gray-600 mb-6">Join a group task by entering an invite code or wait for an invitation.</p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Enter Invite Code</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               x-model="joinCode"
                               placeholder="Enter 8-character code"
                               maxlength="8"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-center text-lg font-mono uppercase tracking-widest focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button @click="joinByCode()" 
                                type="button"
                                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                            Join
                        </button>
                </div>
                    <p class="mt-2 text-xs text-gray-500">Ask the task creator for the invite code</p>
            </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Board View -->
    <div x-show="view === 'board'" class="space-y-6">
        <!-- Add Custom Board Button -->
        <div class="flex justify-end">
            <button @click="openCustomBoardModal()" 
                    type="button"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Custom Board
            </button>
        </div>

        <!-- Dynamic Board Columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($customBoards ?? [] as $board)
                @php
                    $colorClasses = [
                        'pink' => ['from' => 'from-pink-50', 'to' => 'to-white', 'border' => 'border-pink-200', 'text' => 'text-pink-700', 'bg' => 'bg-pink-100', 'hover' => 'bg-pink-50', 'hover-border' => 'border-pink-300'],
                        'orange' => ['from' => 'from-orange-50', 'to' => 'to-white', 'border' => 'border-orange-200', 'text' => 'text-orange-700', 'bg' => 'bg-orange-100', 'hover' => 'bg-orange-50', 'hover-border' => 'border-orange-300'],
                        'purple' => ['from' => 'from-purple-50', 'to' => 'to-white', 'border' => 'border-purple-200', 'text' => 'text-purple-700', 'bg' => 'bg-purple-100', 'hover' => 'bg-purple-50', 'hover-border' => 'border-purple-300'],
                        'blue' => ['from' => 'from-blue-50', 'to' => 'to-white', 'border' => 'border-blue-200', 'text' => 'text-blue-700', 'bg' => 'bg-blue-100', 'hover' => 'bg-blue-50', 'hover-border' => 'border-blue-300'],
                        'green' => ['from' => 'from-green-50', 'to' => 'to-white', 'border' => 'border-green-200', 'text' => 'text-green-700', 'bg' => 'bg-green-100', 'hover' => 'bg-green-50', 'hover-border' => 'border-green-300'],
                        'yellow' => ['from' => 'from-yellow-50', 'to' => 'to-white', 'border' => 'border-yellow-200', 'text' => 'text-yellow-700', 'bg' => 'bg-yellow-100', 'hover' => 'bg-yellow-50', 'hover-border' => 'border-yellow-300'],
                        'red' => ['from' => 'from-red-50', 'to' => 'to-white', 'border' => 'border-red-200', 'text' => 'text-red-700', 'bg' => 'bg-red-100', 'hover' => 'bg-red-50', 'hover-border' => 'border-red-300'],
                        'indigo' => ['from' => 'from-indigo-50', 'to' => 'to-white', 'border' => 'border-indigo-200', 'text' => 'text-indigo-700', 'bg' => 'bg-indigo-100', 'hover' => 'bg-indigo-50', 'hover-border' => 'border-indigo-300'],
                        'gray' => ['from' => 'from-gray-50', 'to' => 'to-white', 'border' => 'border-gray-200', 'text' => 'text-gray-700', 'bg' => 'bg-gray-100', 'hover' => 'bg-gray-50', 'hover-border' => 'border-gray-300'],
                    ];
                    $colors = $colorClasses[$board->color] ?? $colorClasses['gray'];
                    $tasks = $tasksByStatus[$board->status_key] ?? collect();
                @endphp
                <div class="bg-gradient-to-b {{ $colors['from'] }} {{ $colors['to'] }} rounded-lg border {{ $colors['border'] }} {{ $board->is_locked ? 'cursor-default' : 'cursor-move' }} hover:shadow-lg transition-all"
                     data-board-id="{{ $board->id }}"
                     data-is-locked="{{ $board->is_locked ? 'true' : 'false' }}"
                     draggable="{{ $board->is_locked ? 'false' : 'true' }}"
                     @dragstart="handleBoardDragStart($event, {{ $board->id }})"
                     @dragend="handleBoardDragEnd($event)"
                     @dragover.prevent
                     @drop="handleBoardDrop($event, {{ $board->id }})">
                    <div class="p-4 border-b {{ $colors['border'] }}" @click.stop>
                <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 flex-1">
                                @if(!$board->is_locked)
                                <svg class="w-5 h-5 {{ $colors['text'] }} opacity-50 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="cursor: grab;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                </svg>
                                @else
                                <svg class="w-5 h-5 {{ $colors['text'] }} opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Position Locked">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                @endif
                                <h2 class="text-lg font-semibold {{ $colors['text'] }}">{{ $board->name }}</h2>
                </div>
                            <div class="flex items-center space-x-2">
                                <span class="{{ $colors['bg'] }} {{ $colors['text'] }} text-xs font-semibold px-2.5 py-1 rounded-full">{{ $tasks->count() }}</span>
                                <div class="relative" x-data="{ open: false }" @click.stop>
                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600" @click.stop>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                        <div class="py-1">
                                            @if(!$board->is_default)
                                            <button @click="open = false; editCustomBoard({{ $board->id }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</button>
                                            @endif
                                            <button @click="open = false; toggleBoardLock({{ $board->id }})" class="block w-full text-left px-4 py-2 text-sm {{ $board->is_locked ? 'text-green-600' : 'text-gray-700' }} hover:bg-gray-100">
                                                {{ $board->is_locked ? '🔓 Unlock Position' : '🔒 Lock Position' }}
                                            </button>
                                            @if(!$board->is_default)
                                            <button @click="open = false; deleteCustomBoard({{ $board->id }})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
                                            @endif
            </div>
            </div>
        </div>
                            </div>
                </div>
            </div>
            <div class="p-4 space-y-3 min-h-[500px] transition-colors duration-200" 
                         data-status="{{ $board->status_key }}"
                         @drop="handleDrop($event, '{{ $board->status_key }}')" 
                 @dragover.prevent
                 @dragenter.prevent
                         :class="draggedTask ? '{{ $colors['hover'] }} border-2 {{ $colors['hover-border'] }} border-dashed rounded-lg' : ''">
                        @foreach($tasks as $task)
                    @include('admin.tasks.partials.task-card', ['task' => $task, 'type' => $type, 'customPriorities' => $customPriorities ?? collect()])
                @endforeach
            </div>
                </div>
            @endforeach
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
    @include('admin.tasks.partials.task-modal', ['type' => $type, 'users' => $users ?? collect(), 'customBoards' => $customBoards ?? collect()])
    
    <!-- Custom Board Modal -->
    <div x-show="customBoardModalOpen" 
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999]"
         @click.self="closeCustomBoardModal()"
         style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900" x-text="editingCustomBoard ? 'Edit Custom Board' : 'Create Custom Board'"></h3>
                <button @click="closeCustomBoardModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
</div>

            <form @submit.prevent="saveCustomBoard()">
                <div class="space-y-4">
                    <!-- Board Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Board Name *</label>
                        <input type="text" 
                               x-model="customBoardForm.name"
                               required
                               placeholder="e.g., Review, Testing, Blocked"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Color Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                        <div class="grid grid-cols-5 gap-2">
                            @foreach(['pink', 'orange', 'purple', 'blue', 'green', 'yellow', 'red', 'indigo', 'gray'] as $color)
                            <button type="button"
                                    @click="customBoardForm.color = '{{ $color }}'"
                                    :class="customBoardForm.color === '{{ $color }}' ? 'ring-2 ring-offset-2 ring-indigo-500' : ''"
                                    class="h-10 rounded-md border-2 transition-all
                                    @if($color === 'pink') bg-gradient-to-b from-pink-50 to-white border-pink-200
                                    @elseif($color === 'orange') bg-gradient-to-b from-orange-50 to-white border-orange-200
                                    @elseif($color === 'purple') bg-gradient-to-b from-purple-50 to-white border-purple-200
                                    @elseif($color === 'blue') bg-gradient-to-b from-blue-50 to-white border-blue-200
                                    @elseif($color === 'green') bg-gradient-to-b from-green-50 to-white border-green-200
                                    @elseif($color === 'yellow') bg-gradient-to-b from-yellow-50 to-white border-yellow-200
                                    @elseif($color === 'red') bg-gradient-to-b from-red-50 to-white border-red-200
                                    @elseif($color === 'indigo') bg-gradient-to-b from-indigo-50 to-white border-indigo-200
                                    @else bg-gradient-to-b from-gray-50 to-white border-gray-200
                                    @endif">
                                <span class="sr-only">{{ $color }}</span>
                            </button>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Selected: <span x-text="customBoardForm.color" class="font-semibold"></span></p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="closeCustomBoardModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <span x-text="editingCustomBoard ? 'Update Board' : 'Create Board'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Task List Modal (for personal tasks only) -->
    @if($type === 'personal')
    <div x-show="taskListModalOpen" 
         x-cloak
         x-transition
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999]"
         @click.self="closeTaskListModal()">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900" x-text="editingTaskList ? 'Edit Task List' : 'Create New Task List'"></h3>
            <button @click="closeTaskListModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form @submit.prevent="saveTaskList()">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" 
                           x-model="taskListForm.name"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="taskListForm.description"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <select x-model="taskListForm.color"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="blue">Blue</option>
                        <option value="green">Green</option>
                        <option value="yellow">Yellow</option>
                        <option value="red">Red</option>
                        <option value="purple">Purple</option>
                        <option value="pink">Pink</option>
                        <option value="indigo">Indigo</option>
                        <option value="gray">Gray</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        @click="closeTaskListModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    <span x-text="editingTaskList ? 'Update List' : 'Create List'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

    <!-- Custom Priority Modal -->
    <div x-show="customPriorityModalOpen" 
     x-cloak
     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999]"
     @click.self="closeCustomPriorityModal()"
     style="display: none;"
     @open-custom-priority-modal.window="openCustomPriorityModal()">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900" x-text="editingCustomPriority ? 'Edit Custom Priority' : 'Create Custom Priority'"></h3>
            <button @click="closeCustomPriorityModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form @submit.prevent="saveCustomPriority()">
            <div class="space-y-4">
                <!-- Priority Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority Name *</label>
                    <input type="text" 
                           x-model="customPriorityForm.name"
                           required
                           placeholder="e.g., Critical, Important, Nice to Have"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Color Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                    <div class="grid grid-cols-4 gap-2">
                        @php
                            $priorityColors = [
                                'bg-red-200 text-red-900' => 'Red',
                                'bg-orange-200 text-orange-900' => 'Orange',
                                'bg-yellow-100 text-yellow-800' => 'Yellow',
                                'bg-green-100 text-green-800' => 'Green',
                                'bg-blue-100 text-blue-800' => 'Blue',
                                'bg-indigo-100 text-indigo-800' => 'Indigo',
                                'bg-purple-100 text-purple-800' => 'Purple',
                                'bg-pink-100 text-pink-800' => 'Pink',
                                'bg-gray-100 text-gray-800' => 'Gray',
                                'bg-teal-100 text-teal-800' => 'Teal',
                                'bg-cyan-100 text-cyan-800' => 'Cyan',
                                'bg-emerald-100 text-emerald-800' => 'Emerald',
                            ];
                        @endphp
                        @foreach($priorityColors as $colorClass => $colorName)
                        <button type="button"
                                @click="customPriorityForm.color = '{{ $colorClass }}'"
                                :class="customPriorityForm.color === '{{ $colorClass }}' ? 'ring-2 ring-offset-2 ring-indigo-500' : ''"
                                class="h-12 rounded-md border-2 transition-all {{ $colorClass }} border-gray-300 hover:border-gray-400">
                            <span class="text-xs font-medium">{{ $colorName }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Preview -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preview</label>
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="customPriorityForm.color" x-text="customPriorityForm.name || 'Priority Name'"></div>
                </div>

                <!-- Custom Priorities List -->
                @if(isset($customPriorities) && $customPriorities->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Custom Priorities</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-md p-3">
                        @foreach($customPriorities as $customPriority)
                        <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <div class="flex items-center space-x-2">
                                <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $customPriority->color }}">
                                    {{ $customPriority->name }}
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="editCustomPriority({{ $customPriority->id }})" class="text-indigo-600 hover:text-indigo-800 text-xs">
                                    Edit
                                </button>
                                <button type="button" @click="deleteCustomPriority({{ $customPriority->id }})" class="text-red-600 hover:text-red-800 text-xs">
                                    Delete
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" @click="closeCustomPriorityModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                        <span x-text="editingCustomPriority ? 'Update Priority' : 'Create Priority'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

</div>
<!-- End of taskManager scope -->
@endsection
