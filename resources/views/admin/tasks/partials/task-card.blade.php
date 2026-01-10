<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move hover:shadow-md transition-all duration-200"
     draggable="true"
     data-task-id="{{ $task->id }}"
     @dragstart="handleDragStart($event, @js($task))"
     @dragend="draggedTask = null"
     @click="openEditModal(@js($task))">
    <div class="flex items-start justify-between mb-2">
        <h3 class="text-sm font-semibold text-gray-900 flex-1">{{ $task->title }}</h3>
        <button @click.stop="deleteTask({{ $task->id }})" 
                class="text-gray-400 hover:text-red-600 ml-2"
                onclick="event.stopPropagation(); if(confirm('Are you sure you want to delete this task?')) { fetch('/admin/tasks/{{ $task->id }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content } }).then(() => location.reload()); }">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    @if($task->description)
    <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ Str::limit($task->description, 100) }}</p>
    @endif
    
    @if($task->due_date)
    <div class="flex items-center text-xs text-gray-500 mb-2">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
        @if(\Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'done')
            <span class="ml-1 text-red-600 font-semibold">(Overdue)</span>
        @endif
    </div>
    @endif
    
    <div class="flex items-center justify-between mt-3">
        <div class="flex items-center space-x-2">
            @if($task->attachments->count() > 0)
            <span class="text-xs text-gray-500">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                </svg>
                {{ $task->attachments->count() }}
            </span>
            @endif
            @if($task->comments->count() > 0)
            <span class="text-xs text-gray-500">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                {{ $task->comments->count() }}
            </span>
            @endif
        </div>
        @if($task->type === 'group' && $task->assignments->count() > 0)
        <div class="flex -space-x-2">
            @foreach($task->assignments->take(3) as $assignment)
            <img src="{{ $assignment->user->getProfilePictureUrl() }}" 
                 alt="{{ $assignment->user->name }}"
                 class="w-6 h-6 rounded-full border-2 border-white">
            @endforeach
            @if($task->assignments->count() > 3)
            <span class="w-6 h-6 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-xs text-gray-600">+{{ $task->assignments->count() - 3 }}</span>
            @endif
        </div>
        @endif
    </div>
</div>
