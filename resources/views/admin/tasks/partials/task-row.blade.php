<tr class="hover:bg-gray-50 transition-colors">
    <!-- Task -->
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-semibold text-gray-900">{{ $task->title }}</div>
        @if($task->description)
            <div class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $task->description }}</div>
        @endif
    </td>

    <!-- Status -->
    <td class="px-6 py-4 whitespace-nowrap">
        @php
            $statusColors = [
                'todo' => 'bg-gray-100 text-gray-800',
                'in_progress' => 'bg-yellow-100 text-yellow-800',
                'done' => 'bg-green-100 text-green-800',
            ];
            $statusLabel = str_replace('_', ' ', $task->status ?? 'todo');
            $statusClass = $statusColors[$task->status ?? 'todo'] ?? 'bg-gray-100 text-gray-800';
        @endphp
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
            {{ ucfirst($statusLabel) }}
        </span>
    </td>

    <!-- Due Date -->
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
        @if($task->due_date)
            {{ optional($task->due_date)->format('M j, Y') }}
        @else
            <span class="text-gray-400 text-xs">No due date</span>
        @endif
    </td>

    <!-- Assigned To (Group only) -->
    @if($type === 'group')
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
        @php
            $assignees = $task->assignments ?? collect();
        @endphp
        @if($assignees->count() > 0)
            <div class="flex -space-x-2">
                @foreach($assignees->take(5) as $assignment)
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 border border-white text-[11px] font-semibold text-indigo-700">
                        {{ strtoupper(mb_substr($assignment->user->name ?? '?', 0, 1)) }}
                    </span>
                @endforeach
                @if($assignees->count() > 5)
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-200 border border-white text-[11px] text-gray-700">
                        +{{ $assignees->count() - 5 }}
                    </span>
                @endif
            </div>
        @else
            <span class="text-xs text-gray-400">Unassigned</span>
        @endif
    </td>
    @endif

    <!-- Actions -->
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
        <button @click.prevent="openEditModal({ id: {{ $task->id }} })"
                class="text-indigo-600 hover:text-indigo-900">
            Edit
        </button>
        <button @click.prevent="deleteTask({{ $task->id }})"
                class="text-red-600 hover:text-red-900">
            Delete
        </button>
    </td>
</tr>