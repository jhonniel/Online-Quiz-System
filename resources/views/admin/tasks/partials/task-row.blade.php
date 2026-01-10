<tr class="hover:bg-gray-50">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <div>
                <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                @if($task->description)
                <div class="text-sm text-gray-500">{{ Str::limit($task->description, 50) }}</div>
                @endif
            </div>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
            @if($task->status === 'todo') bg-gray-100 text-gray-800
            @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
            @else bg-green-100 text-green-800
            @endif">
            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        @if($task->due_date)
            {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
            @if(\Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'done')
                <span class="text-red-600 font-semibold">(Overdue)</span>
            @endif
        @else
            <span class="text-gray-400">No due date</span>
        @endif
    </td>
    @if($type === 'group')
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex -space-x-2">
            @foreach($task->assignments->take(3) as $assignment)
            <img src="{{ $assignment->user->getProfilePictureUrl() }}" 
                 alt="{{ $assignment->user->name }}"
                 class="w-8 h-8 rounded-full border-2 border-white"
                 title="{{ $assignment->user->name }}">
            @endforeach
            @if($task->assignments->count() > 3)
            <span class="w-8 h-8 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-xs text-gray-600">+{{ $task->assignments->count() - 3 }}</span>
            @endif
        </div>
    </td>
    @endif
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        <button @click="openEditModal(@js($task))" 
                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
        <button onclick="if(confirm('Are you sure?')) { fetch('/admin/tasks/{{ $task->id }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content } }).then(() => location.reload()); }" 
                class="text-red-600 hover:text-red-900">Delete</button>
    </td>
</tr>
