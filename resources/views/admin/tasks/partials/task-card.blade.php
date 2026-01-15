@php
    // Calculate progress percentage (simple calculation based on status)
    $progress = match($task->status) {
        'todo' => 0,
        'in_progress' => 50,
        'done' => 100,
        default => 0
    };
    
    // Priority based on due date or default to medium
    $isOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'done';
    $priority = $isOverdue ? 'high' : 'medium';
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move hover:shadow-md transition-all duration-200"
     draggable="true"
     data-task-id="{{ $task->id }}"
     @dragstart="handleDragStart($event, @js($task))"
     @dragend="draggedTask = null; event.currentTarget.style.opacity = '1';"
     @click="openEditModal(@js($task))">
    
    <!-- Priority Tag -->
    <div class="mb-3">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
            @if($priority === 'high') bg-red-100 text-red-800
            @elseif($priority === 'low') bg-blue-100 text-blue-800
            @else bg-yellow-100 text-yellow-800
            @endif">
            {{ ucfirst($priority) }}
        </span>
    </div>
    
    <!-- Task Title -->
    <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ $task->title }}</h3>
    
    <!-- Description/Note -->
    @if($task->description)
    <p class="text-xs text-gray-600 mb-3 line-clamp-2">{{ Str::limit($task->description, 80) }}</p>
    @endif
    
    <!-- Progress Bar -->
    <div class="mb-3">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-medium text-gray-700">Progress</span>
            <span class="text-xs text-gray-600">{{ $progress }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
        </div>
    </div>
    
    <!-- Footer: Attachments, Comments, Assigned Users -->
    <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
        <div class="flex items-center space-x-3">
            <!-- Attachments -->
            @if($task->attachments->count() > 0)
            <div class="flex items-center text-xs text-gray-600">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                </svg>
                <span class="font-medium">{{ $task->attachments->count() }}</span>
            </div>
            @endif
            
            <!-- Comments -->
            @if($task->comments->count() > 0)
            <div class="flex items-center text-xs text-gray-600">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <span class="font-medium">{{ $task->comments->count() }}</span>
            </div>
            @endif
        </div>
        
        <!-- Assigned Users -->
        @if($type === 'group' && $task->assignments->count() > 0)
        <div class="flex -space-x-2">
            @foreach($task->assignments->take(3) as $assignment)
            <img src="{{ $assignment->user->getProfilePictureUrl() }}" 
                 alt="{{ $assignment->user->name }}"
                 class="w-6 h-6 rounded-full border-2 border-white object-cover"
                 title="{{ $assignment->user->name }}">
            @endforeach
            @if($task->assignments->count() > 3)
            <span class="w-6 h-6 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-xs text-gray-600 font-medium">+{{ $task->assignments->count() - 3 }}</span>
            @endif
        </div>
        @elseif($type === 'personal')
        <div class="flex -space-x-2">
            <img src="{{ $task->creator->getProfilePictureUrl() }}" 
                 alt="{{ $task->creator->name }}"
                 class="w-6 h-6 rounded-full border-2 border-white object-cover"
                 title="{{ $task->creator->name }}">
        </div>
        @endif
    </div>
</div>
