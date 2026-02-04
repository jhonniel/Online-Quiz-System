<div
    class="bg-white/80 backdrop-blur-sm rounded-lg shadow-sm border border-white/60 hover:shadow-md transition-all cursor-pointer group"
    draggable="true"
    @dragstart="handleDragStart($event, { id: {{ $task->id }}, status: '{{ $task->status }}' })"
    @dragend="handleDragEnd($event)"
    @click="openEditModal({ id: {{ $task->id }} })"
>
    <div class="p-3 space-y-2">
        {{-- Title --}}
        <div class="flex items-start justify-between space-x-2">
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900 line-clamp-2">
                    {{ $task->title }}
                </h3>
                @if($task->description)
                    <p class="mt-1 text-xs text-gray-500 line-clamp-2">
                        {{ $task->description }}
                    </p>
                @endif
            </div>

            <div class="flex flex-col items-end space-y-1">
                {{-- Priority badge (built-in + custom label/color) --}}
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
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $priorityClasses }}">
                    {{ ucfirst($priority) }}
                </span>
            </div>
        </div>

        @php
            $attachments = $task->attachments ?? collect();
        @endphp

        {{-- Attachments preview (first few files) --}}
        @if($attachments->count() > 0)
            <div class="mt-2 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-medium text-gray-500">Attachments</span>
                </div>
                <div class="flex items-center space-x-2 overflow-hidden">
                    @foreach($attachments->take(3) as $attachment)
                        @php
                            $fileName = $attachment->file_name ?? '';
                            $fileUrl = $attachment->file_url ?? $attachment->file_path ?? '';
                            $ext = strtolower(pathinfo($fileName ?: $fileUrl, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                        @endphp
                        @if($isImage && $fileUrl)
                            <div class="w-10 h-10 rounded-md overflow-hidden border border-gray-200 flex-shrink-0">
                                <img src="{{ $fileUrl }}"
                                     alt="{{ $fileName ?: 'Attachment' }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-md bg-gray-100 border border-gray-200 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 7v10a4 4 0 008 0V7m-4 0h5a2 2 0 012 2v6a7 7 0 11-14 0V9a2 2 0 012-2h5" />
                                </svg>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between mt-1">
            {{-- Due date --}}
            <div class="flex items-center space-x-1">
                @if($task->due_date)
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-[11px] text-gray-500">
                        {{ optional($task->due_date)->format('M j, Y') }}
                    </span>
                @else
                    <span class="text-[11px] text-gray-400">No due date</span>
                @endif
            </div>

            {{-- Small info chips --}}
            <div class="flex items-center space-x-1">
                @if($type === 'group')
                    @php
                        $assignees = $task->assignments ?? collect();
                    @endphp
                    @if($assignees->count() > 0)
                        <div class="flex -space-x-1">
                            @foreach($assignees->take(3) as $assignment)
                                <div class="w-5 h-5 rounded-full bg-indigo-100 border border-white flex items-center justify-center">
                                    <span class="text-[9px] font-semibold text-indigo-700">
                                        {{ strtoupper(mb_substr($assignment->user->name ?? '?', 0, 1)) }}
                                    </span>
                                </div>
                            @endforeach
                            @if($assignees->count() > 3)
                                <div class="w-5 h-5 rounded-full bg-gray-100 border border-white flex items-center justify-center">
                                    <span class="text-[9px] text-gray-500">+{{ $assignees->count() - 3 }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                @php
                    $attachmentsCount = $task->attachments->count() ?? 0;
                    $commentsCount = $task->comments->count() ?? 0;
                @endphp

                @if($attachmentsCount > 0)
                    <span class="inline-flex items-center text-[10px] text-gray-500 px-1.5 py-0.5 rounded-full bg-gray-100">
                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 12.79V7a2 2 0 00-2-2h-5.79M7 7h.01M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                        </svg>
                        {{ $attachmentsCount }}
                    </span>
                @endif

                @if($commentsCount > 0)
                    <span class="inline-flex items-center text-[10px] text-gray-500 px-1.5 py-0.5 rounded-full bg-gray-100">
                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 8h10M7 12h6m-6 4h4m1 4l-4-4H6a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        {{ $commentsCount }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

