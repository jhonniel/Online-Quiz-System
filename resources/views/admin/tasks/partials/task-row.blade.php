<tr class="hover:bg-gray-50 transition-colors">
    <!-- Task -->
    <td class="px-6 py-4">
        <div class="text-sm font-semibold text-gray-900">{{ $task->title }}</div>
        @if($task->description)
            <div class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $task->description }}</div>
        @endif
        @if($task->attachments && $task->attachments->count() > 0)
            <div class="mt-2 space-y-2">
                <span class="text-xs text-gray-500 font-medium">Attachments:</span>
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach($task->attachments->take(4) as $attachment)
                        @php
                            $fileName = $attachment->file_name ?? '';
                            $fileUrl = $attachment->file_url ?? '';
                            $ext = strtolower(pathinfo($fileName ?: $fileUrl, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                        @endphp
                        @if($isImage && $fileUrl)
                            <button @click.prevent="openAttachmentPreview({
                                id: {{ $attachment->id }},
                                file_name: '{{ addslashes($attachment->file_name) }}',
                                file_url: '{{ $attachment->file_url }}',
                                file_type: '{{ $attachment->file_type ?? '' }}'
                            })"
                                    class="relative group w-16 h-16 rounded-md overflow-hidden border-2 border-gray-200 hover:border-indigo-400 transition-all cursor-pointer flex-shrink-0 shadow-sm hover:shadow-md">
                                <img src="{{ $fileUrl }}"
                                     alt="{{ $fileName ?: 'Attachment' }}"
                                     class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-opacity flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </button>
                        @else
                            <button @click.prevent="openAttachmentPreview({
                                id: {{ $attachment->id }},
                                file_name: '{{ addslashes($attachment->file_name) }}',
                                file_url: '{{ $attachment->file_url }}',
                                file_type: '{{ $attachment->file_type ?? '' }}'
                            })"
                                    class="relative group w-16 h-16 rounded-md bg-gray-100 border-2 border-gray-200 hover:border-indigo-400 transition-all cursor-pointer flex-shrink-0 shadow-sm hover:shadow-md flex items-center justify-center">
                                @if($ext === 'pdf')
                                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                @elseif(in_array($ext, ['doc', 'docx']))
                                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                @elseif(in_array($ext, ['mp4', 'avi', 'mov', 'wmv']))
                                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                @else
                                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7v10a4 4 0 008 0V7m-4 0h5a2 2 0 012 2v6a7 7 0 11-14 0V9a2 2 0 012-2h5"></path>
                                    </svg>
                                @endif
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-opacity"></div>
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-60 text-white text-[9px] px-1 py-0.5 truncate opacity-0 group-hover:opacity-100 transition-opacity">
                                    {{ $fileName }}
                                </div>
                            </button>
                        @endif
                    @endforeach
                    @if($task->attachments->count() > 4)
                        <button @click.prevent="openEditModal({ id: {{ $task->id }} })"
                                class="w-16 h-16 rounded-md bg-gray-50 border-2 border-dashed border-gray-300 hover:border-indigo-400 transition-all cursor-pointer flex-shrink-0 flex items-center justify-center group">
                            <div class="text-center">
                                <span class="text-xs font-medium text-gray-600 group-hover:text-indigo-600">+{{ $task->attachments->count() - 4 }}</span>
                                <span class="text-[9px] text-gray-500 block">more</span>
                            </div>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </td>

    <!-- Priority -->
    <td class="px-6 py-4 whitespace-nowrap">
        @php
            $priority = $task->priority ?? 'medium';
            // Try to find matching custom priority for this name
            $customPriority = (isset($customPriorities) && $customPriorities instanceof \Illuminate\Support\Collection)
                ? $customPriorities->firstWhere('name', $priority)
                : null;

            if ($customPriority) {
                // Color comes from DB, e.g. 'bg-red-100 text-red-800'
                $priorityClasses = $customPriority->color;
            } else {
                // Fallback to default mapping
                $priorityClasses = match($priority) {
                    'high' => 'bg-red-100 text-red-700',
                    'low' => 'bg-emerald-100 text-emerald-700',
                    default => 'bg-amber-100 text-amber-700',
                };
            }
        @endphp
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityClasses }}">
            {{ ucfirst($priority) }}
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