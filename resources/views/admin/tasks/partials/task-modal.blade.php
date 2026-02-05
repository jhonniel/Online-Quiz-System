<div id="task-modal"
     x-data="taskModal()"
     x-cloak
     class="fixed inset-0 z-[9998] flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900" x-text="currentTask ? 'Edit Task' : 'Create Task'"></h2>
                <p class="text-xs text-gray-500 mt-1">
                    <span class="uppercase tracking-wide text-[10px] px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600">
                        {{ strtoupper($type) }} TASK
                    </span>
                </p>
            </div>
            <button type="button"
                    class="text-gray-400 hover:text-gray-600"
                    @click="closeModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text"
                       x-model="formData.title"
                       placeholder="e.g., Prepare weekly report"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea x-model="formData.description"
                          rows="3"
                          placeholder="Add more details about this task..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea x-model="formData.notes"
                          rows="2"
                          placeholder="Internal notes or comments..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="formData.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                </div>

                <!-- Priority (built-in + custom) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <div class="flex items-center space-x-2">
                        <select x-model="formData.priority"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            @if(isset($customPriorities) && $customPriorities->count() > 0)
                                <option disabled>──────────</option>
                                @foreach($customPriorities as $cp)
                                    <option value="{{ $cp->name }}">{{ $cp->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <button type="button"
                                class="px-2 py-1 text-xs rounded-md border border-indigo-200 text-indigo-600 hover:bg-indigo-50"
                                @click.prevent="$dispatch('open-custom-priority-modal')">
                            + New
                        </button>
                    </div>
                    @if(isset($customPriorities) && $customPriorities->count() > 0)
                        <p class="mt-1 text-xs text-gray-500">Pick a built-in or one of your saved custom priorities.</p>
                    @else
                        <p class="mt-1 text-xs text-gray-500">No custom priorities yet. Click “New” to create one.</p>
                    @endif
                </div>
            </div>

            <!-- Due date/time -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                    <input type="date"
                           x-model="formData.due_date"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Time</label>
                    <input type="time"
                           x-model="formData.due_time"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            @if($type === 'group')
                <!-- Assigned users (group tasks only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                    <select multiple
                            x-model="formData.assigned_users"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Hold Cmd (Mac) or Ctrl (Windows) to select multiple users.</p>
                </div>
            @endif

            <!-- File attachment (hooked by JS) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Attachment</label>
                <input id="task-file-input"
                       type="file"
                       @change="handleFileSelect($event)"
                       class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="mt-1 text-xs text-gray-500">Optional: attach a file related to this task.</p>

                <!-- Existing attachments preview/list -->
                <template x-if="currentTask && currentTask.attachments && currentTask.attachments.length">
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-600">Existing Attachments</span>
                        </div>
                        <div class="flex gap-3 overflow-x-auto py-1">
                            <template x-for="att in currentTask.attachments" :key="att.id">
                                <div class="bg-gray-900 text-white rounded-lg overflow-hidden shadow flex-shrink-0 w-40 cursor-pointer"
                                     @click.stop="openAttachmentPreview(att)">
                                    <div class="w-full h-24 bg-gray-800 flex items-center justify-center overflow-hidden">
                                        <template x-if="att.file_url && ['jpg','jpeg','png','gif','webp'].includes((att.file_name || att.file_url || '').split('.').pop().toLowerCase())">
                                            <img :src="att.file_url"
                                                 :alt="att.file_name || 'Attachment'"
                                                 class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="! (att.file_url && ['jpg','jpeg','png','gif','webp'].includes((att.file_name || att.file_url || '').split('.').pop().toLowerCase()))">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M7 7v10a4 4 0 008 0V7m-4 0h5a2 2 0 012 2v6a7 7 0 11-14 0V9a2 2 0 012-2h5" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="px-3 py-2 space-y-0.5">
                                        <p class="text-xs font-medium truncate" x-text="att.file_name || 'Attachment'"></p>
                                        <p class="text-[10px] text-gray-400" x-text="att.created_at ? new Date(att.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }) : ''"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between space-x-3">
            <div>
                <!-- Delete button only when editing an existing task -->
                <template x-if="currentTask && currentTask.id">
                    <button type="button"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 bg-white border border-red-200 rounded-md hover:bg-red-50"
                            @click.prevent="openDeleteConfirm()">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0h8m-5-3h2a1 1 0 011 1v2H9V5a1 1 0 011-1z" />
                        </svg>
                        Delete Task
                    </button>
                </template>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        @click="closeModal()">
                    Cancel
                </button>
                <button type="button"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed"
                        @click="saveTask()"
                        :disabled="isSaving">
                    <span x-show="!isSaving" x-text="currentTask ? 'Save Changes' : 'Create Task'"></span>
                    <span x-show="isSaving" class="inline-flex items-center space-x-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3.5-3.5L12 0v4a8 8 0 100 16v-4l-3.5 3.5L12 24v-4a8 8 0 01-8-8z"></path>
                        </svg>
                        <span>Saving...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div x-show="deleteConfirmOpen"
         x-cloak
         class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Delete Task</h3>
                <p class="mt-1 text-sm text-gray-600">
                    This action cannot be undone. To confirm, type <span class="font-semibold">DELETE</span> and enter your password.
                </p>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Type DELETE to confirm
                    </label>
                    <input type="text"
                           x-model="deleteConfirmForm.confirm_text"
                           placeholder="DELETE"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 uppercase">
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <button type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        @click="closeDeleteConfirm()">
                    Cancel
                </button>
                <button type="button"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        @click="confirmDeleteTask()">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Attachment Preview Modal -->
    <div x-show="previewAttachmentOpen"
         x-cloak
         class="fixed inset-0 z-[10010] flex items-center justify-center bg-black bg-opacity-80">
        <div class="relative max-w-4xl w-full mx-4">
            <button type="button"
                    class="absolute top-3 right-3 text-white hover:text-gray-300"
                    @click="closeAttachmentPreview()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="bg-gray-900 rounded-xl overflow-hidden shadow-2xl">
                <div class="px-4 py-3 border-b border-gray-800 flex items-center justify-between">
                    <div class="text-sm text-gray-100 truncate" x-text="previewAttachment ? (previewAttachment.file_name || 'Attachment') : ''"></div>
                </div>
                <div class="bg-black flex items-center justify-center max-h-[80vh]">
                    <template x-if="previewAttachment && previewAttachment.file_url">
                        <img :src="previewAttachment.file_url"
                             :alt="previewAttachment.file_name || 'Attachment'"
                             class="max-h-[78vh] max-w-full object-contain">
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Modal for Task Modal -->
    <div x-show="alertModal.open" 
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[10020]"
         @click.self="closeAlert()"
         style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold" 
                    :class="{
                        'text-blue-600': alertModal.type === 'info',
                        'text-green-600': alertModal.type === 'success',
                        'text-red-600': alertModal.type === 'error',
                        'text-yellow-600': alertModal.type === 'warning'
                    }"
                    x-text="alertModal.title"></h3>
                <button @click="closeAlert()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-700 whitespace-pre-line" x-text="alertModal.message"></p>
            </div>
            <div class="flex justify-end">
                <button @click="closeAlert()" 
                        class="px-4 py-2 rounded-md text-sm font-medium"
                        :class="{
                            'bg-blue-600 text-white hover:bg-blue-700': alertModal.type === 'info',
                            'bg-green-600 text-white hover:bg-green-700': alertModal.type === 'success',
                            'bg-red-600 text-white hover:bg-red-700': alertModal.type === 'error',
                            'bg-yellow-600 text-white hover:bg-yellow-700': alertModal.type === 'warning'
                        }">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Confirm Modal for Task Modal -->
    <div x-show="confirmModal.open" 
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[10020]"
         @click.self="handleCancel()"
         style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900" x-text="confirmModal.title"></h3>
                <button @click="handleCancel()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-700 whitespace-pre-line" x-text="confirmModal.message"></p>
            </div>
            <div class="flex justify-end space-x-3">
                <button @click="handleCancel()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button @click="handleConfirm()" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

